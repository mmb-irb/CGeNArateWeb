#!/usr/bin/env python3
import redis
import json
import os
import argparse
import sys
from datetime import datetime


redis_host = os.environ.get('REDIS_HOST', 'shared_redis')

# Connect to shared Redis instead of local Redis
r = redis.Redis(host=redis_host, decode_responses=True)


def get_job_status(job_id):
    """
    Retrieve comprehensive job status from Redis
    """
    try:

        # Check if job exists
        if not r.sismember("all_jobs", job_id):
            return {
                "error": f"Job {job_id} not found",
                "exists": False
            }

        # Gather all job information
        job_data = {
            "id": job_id,
            "exists": True
        }

        # Get basic job information
        job_keys = [
            "queue", "worker", "command", "status",
            "started_at", "finished_at", "volumes",
            "error", "exit_code", "output"
        ]

        for key in job_keys:
            value = r.get(f"job:{job_id}:{key}")
            if value is not None:
                # Try to parse JSON for volumes
                if key == "volumes" and value:
                    try:
                        job_data[key] = json.loads(value)
                    except json.JSONDecodeError:
                        job_data[key] = value
                else:
                    job_data[key] = value

        # Get additional metadata if available
        created_at = r.get(f"job:{job_id}:created_at")
        if created_at:
            job_data["created_at"] = created_at

        # Calculate duration if both start and finish times exist
        if job_data.get("started_at") and job_data.get("finished_at"):
            try:
                start = datetime.fromisoformat(job_data["started_at"].replace('Z', '+00:00'))
                finish = datetime.fromisoformat(job_data["finished_at"].replace('Z', '+00:00'))
                duration = (finish - start).total_seconds()
                job_data["duration_seconds"] = duration
            except ValueError:
                # If datetime parsing fails, skip duration calculation
                pass

        return job_data

    except redis.ConnectionError as e:
        return {
            "error": f"Could not connect to Redis at {redis_host}: {e}",
            "exists": False
        }
    except Exception as e:
        return {
            "error": f"Unexpected error: {e}",
            "exists": False
        }


def main():
    parser = argparse.ArgumentParser(description='Get job status from Redis queue')
    parser.add_argument('job_id', help='Job ID to retrieve status for')

    args = parser.parse_args()

    # Single status check
    job_status = get_job_status(args.job_id)

    print(json.dumps(job_status))

    # Exit with error code if job doesn't exist or has error
    if not job_status.get("exists", False) or "error" in job_status:
        sys.exit(1)


if __name__ == '__main__':
    main()
