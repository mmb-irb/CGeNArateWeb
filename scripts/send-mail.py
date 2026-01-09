#!/usr/bin/env python3
import docker
import os
import sys
import argparse
import json
import re


def replace_template_vars(html_content, variables):
    """
    Replace Twig-style variables in HTML content
    Supports both {{ variable }} and {% if variable %}...{% endif %} (simple replacement)
    """
    if not variables:
        return html_content

    result = html_content

    for key, value in variables.items():
        # Replace {{ variable }} style
        result = result.replace('{{ ' + key + ' }}', str(value))
        result = result.replace('{{' + key + '}}', str(value))

        # Also support simple conditionals by removing empty ones
        # This is a simple approach - not full Twig rendering
        if not value or value == '':
            # Remove blocks with empty variables
            # Remove {% if key %}...{% endif %} blocks for empty values
            pattern = r'\{%\s*if\s+' + re.escape(key) + r'\s*%\}.*?\{%\s*endif\s*%\}'
            result = re.sub(pattern, '', result, flags=re.DOTALL)

    return result


def send_email_with_container(html_body, to, subject):
    try:
        client = docker.DockerClient(base_url='unix://var/run/docker.sock')
    except Exception as e:
        return False, f"❌ Could not connect to Docker: {e}"

    # Read credentials from environment variables
    env = {
        "CLIENT_ID": os.environ.get('CLIENT_ID'),
        "CLIENT_SECRET": os.environ.get('CLIENT_SECRET'),
        "REFRESH_TOKEN": os.environ.get('REFRESH_TOKEN'),
        "TO": to,
        "SUBJECT": subject,
        "HTML_BODY": html_body,
    }

    # Run container with HTML body as environment variable
    try:
        container = client.containers.run(
            image=os.environ.get('MAIL_SENDER_IMAGE'),
            environment=env,
            detach=True,
            remove=False,  # Don't auto-remove so we can read logs
        )

        # Wait for container to finish
        result = container.wait()

        # Get logs before removing
        try:
            logs = container.logs().decode()
        except Exception as log_error:
            logs = f"Could not retrieve logs: {log_error}"

        # Now remove the container
        try:
            container.remove()
        except Exception:
            pass  # Container might already be removed

        if result["StatusCode"] == 0:
            return True, "✅ Email sent successfully"
        else:
            return False, f"❌ Email sending failed\n{logs}"

    except Exception as e:
        return False, f"❌ Error running container: {e}"


def main():
    parser = argparse.ArgumentParser(description='Send email via mail_sender container')
    parser.add_argument('--to', required=True, help='Recipient email address')
    parser.add_argument('--subject', required=True, help='Email subject')

    # Allow either HTML content or file path
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument('--html', help='HTML body content as string')
    group.add_argument('--html-file', help='Path to HTML file')

    # Template variables as JSON string
    parser.add_argument('--vars', help='Template variables as JSON string (e.g., \'{"name":"value"}\')')

    args = parser.parse_args()

    # Read HTML body from file or use provided string
    if args.html_file:
        try:
            with open(args.html_file, 'r', encoding='utf-8') as f:
                html_body = f.read()
        except Exception as e:
            print(f"❌ Error reading HTML file: {e}", file=sys.stderr)
            sys.exit(1)
    else:
        html_body = args.html

    # Parse template variables if provided
    variables = {}
    if args.vars:
        try:
            variables = json.loads(args.vars)
        except json.JSONDecodeError as e:
            print(f"❌ Error parsing variables JSON: {e}", file=sys.stderr)
            sys.exit(1)

    # Replace template variables
    if variables:
        html_body = replace_template_vars(html_body, variables)

    # Send email
    success, message = send_email_with_container(html_body, args.to, args.subject)
    print(message)

    sys.exit(0 if success else 1)


if __name__ == '__main__':
    main()
