# JWT vs. Joomla Session Token

Some of these controllers use [JSON Web Token, JWT](https://jwt.io) instead of the standard Joomla session token verification.

JWT Library used is [firebase/php-jwt](https://github.com/firebase/php-jwt).

This model permits passwordless access to limited functions. Instead, the user is asked for what function
they want to perform (e.g., badge print, attendee checkin, etc.) and to provide
the account email. If the email is associated with an account that is in the approved group, an email is
sent to the email address to confirm the request. When that link is clicked, it contains a version of the
JWT that lets the status get updated (usually it's going to be an approval). Upon approval, the database 
is updated so the state of the JWT is issued. We have a little JavaScript loop that monitors for this
to happen and the user is redirected to the appropriate View based on the subject of the JWT.

## JWT Payload Keys

| Key | Description |
|-----|-------------|
| nonce | Session ID (same as Joomla session) |
| email | Requested Email Address |
| subject | Specific function to be performed (defined in `\\ClawCorpLib\\Lib\\Jwtwrapper::jwt_token_pages`) |
| state | One of `\\ClawCorpLib\\Enum\\JwtStates`  |

  1. The keys of `jwt_token_pages` give the identifier of the JWT role.
  2. If a user (via email lookup) is in the (currently hard-coded) user group, a JWT can be initialized.
  3. After initialization, an email is sent with a "CONFIRM" and a "REVOKE" option.
  4. If confirmed, the token becomes valid for use.

## State Transitions

```text
init -> email does not exist -> error
     -> user not in group -> error
     -> email:confirm received -> issued
     -> email:revoked received -> revoked

issued -> exp > now() -> expired
```

`email:confirm` must be received within 310 seconds (a tad more than 5 minutes). `email:revoked` can
be received at any time.

**User Interface Note:** A super admin may directly set state to `issued` or `revoked`. This allows bypassing the email verification.

## Validity time

The length in seconds from init to when the token is no longer considered valid.

There is no mechanism for extending the validity time.

## Validation

There are two main calls for token validation:

  1. `Jwtwrapper::valid()` returns a bool (true=valid) for additional handling
  1. `Jwtwrapper::redirectOnInvalidToken()` causes an immediate redirect if not valid (note: uses a hidden `/link` menu item)

We use a combination of Joomla session token AND subject to identify the JWT in the database. When created,
a random password is created. In this way, the JWT can be validated using JWT principals.

Currently, we are using the HS384 signing algorithm. It's simple enough to deal with. YMMV.

The JWT payload is decoded to retrieve the session and subject. The session must match the user's current
Joomla session, and the subject is supplied in the Controller code or posted from the web form. The signature
of the JWT is verified using the stored password.

There are probably some holes, but getting the Joomla session token information would require accessing the browser
data for someone using the JWT. We consider this low risk in the context of who will be using this methodology for
authentication. For critical roles, one should use standard authentication and verification methods (i.e., password+2FA).
