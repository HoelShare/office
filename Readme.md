# Setup

## Local
### Requirements
* php 8
* mysql Database

### Steps
1. Create a `.env.local` File
1. Configure
    1. DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
    1. LDAP_SERVER="localhost"
    1. LDAP_SEARCH_DN=""
    1. LDAP_SEARCH_PASS=""
    1. LDAP_BASE_DN=""
    1. LDAP_LOGIN_DN="company\{username}"
