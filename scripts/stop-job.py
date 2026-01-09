#!/usr/bin/env python3
import redis
import docker
import time
import json
import os
import argparse
import sys
# import subprocess
from datetime import datetime


redis_host = os.environ.get('REDIS_HOST', 'shared_redis')

# Connect to shared Redis instead of local Redis
r = redis.Redis(host=redis_host, decode_responses=True)


def get_job_info(job_id):
    """
    Get job information from Redis
    """
    try:

        if not r.sismember("all_jobs", job_id):
            return None, f"Job {job_id} not found"

        job_data = {}
        job_keys = ["queue", "worker", "command", "container", "status", "started_at"]

        for key in job_keys:
            value = r.get(f"job:{job_id}:{key}")
            if value is not None:
                job_data[key] = value

        return job_data, None

    except redis.ConnectionError as e:
        return None, f"Could not connect to Redis at {redis_host}: {e}"
    except Exception as e:
        return None, f"Unexpected error: {e}"


def stop_job_via_docker(job_id):
    """
    Try to stop job via Docker API
    """
    container_id = r.get(f"job:{job_id}:container")
    if container_id:
        try:
            client = docker.DockerClient(base_url='unix://var/run/docker.sock')
            container = client.containers.get(container_id)
            container.kill()
            time.sleep(1)
            r.set(f"job:{job_id}:status", "stopped")
            job_info = {
                "id": job_id,
                "status": "stopped",
                "container": container.short_id,
                "method": "docker_api"
            }
            return True, json.dumps(job_info)

        except Exception as e:
            # Docker API failed, fall back to Redis-only method
            return stop_job_via_redis_only(job_id, str(e))
    else:
        # No container ID, use Redis-only method
        return stop_job_via_redis_only(job_id, "No container ID found")


def stop_job_via_redis_only(job_id, reason="Manual cancellation"):
    """
    Stop job by marking it as stopped in Redis (fallback method)
    """
    try:
        now = datetime.now().isoformat()

        # Mark job as stopped
        r.set(f"job:{job_id}:status", "stopped")
        r.set(f"job:{job_id}:finished_at", now)
        r.set(f"job:{job_id}:stopped_by", "stop-job.py")
        r.set(f"job:{job_id}:cancel_reason", reason)

        # Send cancellation signal to the queue system
        # This depends on how your queue system handles cancellations
        try:
            # Try to add a cancellation message to a cancellation queue
            cancel_data = {
                "job_id": job_id,
                "action": "cancel",
                "timestamp": now,
                "reason": reason
            }
            r.lpush("job_cancellations", json.dumps(cancel_data))
        except Exception:
            # If cancellation queue doesn't work, that's OK
            pass

        job_info = {
            "id": job_id,
            "status": "stopped",
            "method": "redis_only",
            "reason": reason,
            "stopped_at": now
        }
        return True, json.dumps(job_info)

    except Exception as e:
        job_info = {
            "id": job_id,
            "status": "error",
            "error": f"Could not stop job in Redis: {e}"
        }
        return False, json.dumps(job_info)


def stop_job_via_queue_client(job_id):
    """
    Stop job using best available method
    """
    # First try Docker API if we have a container ID
    container_id = r.get(f"job:{job_id}:container")
    if container_id:
        try:
            # Try Docker API first
            return stop_job_via_docker(job_id)
        except Exception as e:
            # If Docker fails, fall back to Redis-only
            return stop_job_via_redis_only(job_id, f"Docker API failed: {e}")
    else:
        # No container ID, use Redis-only method
        return stop_job_via_redis_only(job_id, "No container ID available")


def update_job_status(job_id):
    """
    Update job status to 'stopped' in Redis
    """
    try:
        r = redis.Redis(host=redis_host, decode_responses=True)

        # Set status to stopped and add finished timestamp
        now = datetime.now().isoformat()
        r.set(f"job:{job_id}:status", "stopped")
        r.set(f"job:{job_id}:finished_at", now)
        r.set(f"job:{job_id}:stopped_by", "stop-job.py")

        return True, "Job status updated to stopped"

    except Exception as e:
        return False, f"Could not update job status: {e}"


def main():
    parser = argparse.ArgumentParser(description='Stop a running job via queue_client')
    parser.add_argument('job_id', help='Job ID to stop')
    parser.add_argument('--force', action='store_true',
                        help='Force stop even if job appears to be already finished')
    parser.add_argument('--redis-only', action='store_true',
                        help='Skip Docker API and only update Redis status (useful when Docker is not available)')

    args = parser.parse_args()

    result = {
        "job_id": args.job_id,
        "success": False,
        "message": "",
        "job_info": None,
        "actions_taken": []
    }

    # Get job information first
    job_info, error = get_job_info(args.job_id)
    if error:
        result["message"] = error
        print(json.dumps(result))
        sys.exit(1)

    result["job_info"] = job_info

    # Check if job is already finished
    current_status = job_info.get("status", "unknown")
    if current_status in ["done", "failed", "stopped"] and not args.force:
        result["message"] = f"Job {args.job_id} is already {current_status}. Use --force to stop anyway."
        print(json.dumps(result))
        sys.exit(0)

    # if not args.json:
    #     print(f"Job {args.job_id} info:")
    #     print(f"  Status: {current_status}")
    #     print(f"  Worker: {job_info.get('worker', 'unknown')}")
    #     print(f"  Queue: {job_info.get('queue', 'unknown')}")
    #     print(f"  Command: {job_info.get('command', 'unknown')}")
    #     print()

    # Stop the job via queue_client or Redis-only
    if args.redis_only:
        success, message = stop_job_via_redis_only(args.job_id, "Manual cancellation (redis-only mode)")
    else:
        success, message = stop_job_via_queue_client(args.job_id)

    if success:
        result["actions_taken"].append("queue_client_stop")
        result["success"] = True

        # print(json.dumps(result))

        # if not args.json:
        #     print(f"✓ Stop command executed: {message}")

        # Update status in Redis
        status_success, status_message = update_job_status(args.job_id)
        if status_success:
            result["actions_taken"].append("status_updated")
            result["message"] = status_message
            print(json.dumps(result))
            # if not args.json:
            #     print(f"✓ Status updated: {status_message}")
        else:
            # if not args.json:
            #     print(f"⚠ Could not update status: {status_message}")
            result["message"] = status_message
            print(json.dumps(result))

        result["message"] = message

    else:
        result["message"] = f"Failed to stop job: {message}"
        print(json.dumps(result))
        # if not args.json:
        #     print(f"✗ Failed to stop job: {message}")

    # Output final result
    # if args.json:
    # print(json.dumps(result))
    # else:
    #     print(f"\nJob {args.job_id} stop {'done' if result['success'] else 'failed'}")

    sys.exit(0 if result["success"] else 1)


if __name__ == '__main__':
    main()
