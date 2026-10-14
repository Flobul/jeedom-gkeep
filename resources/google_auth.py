#!/usr/bin/env python3
"""Exchange the browser cookie over stdin; never emit credentials on failure."""
import json
import re
import signal
import sys


class AuthenticationError(Exception):
    def __init__(self, code, detail=''):
        super().__init__(code)
        self.code = code
        self.detail = str(detail)[:200]


def exchange(payload):
    try:
        import gpsoauth
        import gkeepapi
    except ModuleNotFoundError as error:
        raise AuthenticationError('dependency_missing', error.name or '') from error
    email = str(payload.get('email', '')).strip()
    cookie = str(payload.get('oauth_token', '')).strip()
    android = str(payload.get('android_id', '')).strip()
    if not email or not cookie or not re.fullmatch(r'[0-9a-fA-F]{16}', android):
        raise AuthenticationError('invalid_input')
    response = gpsoauth.exchange_token(email, cookie, android)
    token = response.get('Token')
    if not token:
        detail = response.get('Error') or response.get('ErrorDetail') or 'no_token'
        raise AuthenticationError('exchange_refused', detail)
    keep = gkeepapi.Keep()
    try:
        if hasattr(keep, 'authenticate'):
            keep.authenticate(email, token, device_id=android)
        else:
            keep.resume(email, token, device_id=android)
    except Exception as error:
        raise AuthenticationError('keep_refused', error.__class__.__name__) from error
    return token


def main():
    signal.signal(signal.SIGALRM, lambda *_: (_ for _ in ()).throw(TimeoutError()))
    signal.alarm(90)
    try:
        payload = json.loads(sys.stdin.read(32768))
        token = exchange(payload)
        print(json.dumps({'token':token}))
    except AuthenticationError as error:
        print(json.dumps({'error':'authentication_failed', 'code':error.code, 'detail':error.detail}))
        return 1
    except Exception as error:
        print(json.dumps({'error':'authentication_failed', 'code':'unexpected_error', 'detail':error.__class__.__name__}))
        return 1
    finally:
        signal.alarm(0)
    return 0


if __name__ == '__main__':
    sys.exit(main())
