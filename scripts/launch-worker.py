#!/usr/bin/env python3
import redis
import json
import os
import argparse
from datetime import datetime

redis_host = os.environ.get('REDIS_HOST', 'shared_redis')
qname = os.environ.get('QUEUE_NAME')
worker = os.environ.get('WORKER_IMAGE')

# Connect to shared Redis instead of local Redis
r = redis.Redis(host=redis_host, decode_responses=True)


def submit_job(worker_image, command, queue_name, volumes=None):
    job_id = os.urandom(4).hex()
    now = datetime.now().isoformat()

    r.set(f"job:{job_id}:queue", queue_name)
    r.set(f"job:{job_id}:worker", worker_image)
    r.set(f"job:{job_id}:command", command)
    r.set(f"job:{job_id}:status", "queued")
    r.set(f"job:{job_id}:started_at", now)

    if volumes:
        r.set(f"job:{job_id}:volumes", json.dumps(volumes))

    r.rpush(queue_name, job_id)
    r.sadd("all_jobs", job_id)

    return job_id


def main():
    parser = argparse.ArgumentParser(description='Launch a worker job via Redis queue')
    parser.add_argument('--command', required=True, help='Command to execute in the worker')
    parser.add_argument('--volumes', help='JSON string of volume mappings (optional)', default=None)

    args = parser.parse_args()

    # Parse volumes if provided
    volumes = None
    if args.volumes:
        try:
            volumes = json.loads(args.volumes)
        except json.JSONDecodeError as e:
            print(f"Error parsing volumes JSON: {e}")
            return 1

    # Submit the job
    try:
        job_id = submit_job(
            worker_image=worker,
            command=args.command,
            queue_name=qname,
            volumes=volumes
        )

        job_info = {
            "job_id": job_id,
            "queue": qname,
            "worker": worker
        }
        print(json.dumps(job_info, separators=(',', ':')))

        return 0
    except Exception as e:
        print(f"Error submitting job: {e}")
        import traceback
        traceback.print_exc()
        return 1


if __name__ == '__main__':
    exit(main())
