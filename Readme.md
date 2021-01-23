# Setup

## Steps
1. Create a `.env.local` File
1. Set configuration in newly created file, for example:
   1. DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
   1. LDAP_SERVER="localhost"
   1. LDAP_SEARCH_DN=""
   1. LDAP_SEARCH_PASS=""
   1. LDAP_BASE_DN=""
   1. LDAP_LOGIN_DN="company\{username}"
1. follow [Local](#Local) or [Docker](#Docker) instructions

## Local
### Requirements
* webserver with php 8
* mysql Database

### further steps
4. install application with `composer install`
1. configure webserver to look at `public` folder

## Docker
4. run Docker containers: `docker-compose up -d`
   1. [OPTIONAL] configure user in `docker-compose.override.yml`
      ```
      services:
         app:
            user: "${UID}:${GID}"
      ```
1. Interact with App Docker Container `docker exec -w /app -it office_app /bin/bash`
1. Install application with `composer install`
1. Application runs at [localhost:8090](http://localhost:8090/)

### Configure SAML
- Metadata URL
- Entity ID

Server Site:
- Trusted Sites (From / Callback)
- Endpoint to AssertionConsumer (possible the same like trusted sites)
- Which information will be send