# DB Sanitize Scripts

In order to test complex migrations or to ensure that new business logic covers all cases we might see with data in the production environment, it is sometimes necessary to bring down a sanitized copy of the production database into the development environment. Sanitization is the process of removing all accessible personally identifiable information, as well as any system settings and/or passwords that are specific to the production environment. This page details how we sanitize data, and keeps track of sanitization scripts and schemas for each sanitization.

## Policy
Per our Information Handling Policy and other related policies, we must be careful with the transmission, storage, access, and display of sensitive information.
In this scenario, only "need to know" information should pass to the development team, which does not include any personally identifiable information (PII) of our users.  It does include the relationships between entities within the database, as well as the ability to join tables based on unique data.  Therefore, we will "dispose" and "destroy" the PII data in a way that is "undecipherable and cannot be reconstructed."

## Usage

1. upload this directory to database server (db01)

        upload_to_db.sh

2. run sanitization

        ssh db01
        cd db_sanitize
        sanitize_db.sh

3. Deliver the .tar.gz dump file to the development team.


## Further Reading
See [https://credit.atlassian.net/wiki/display/RT/Database+Sanitzation#DatabaseSanitzation-CreateSnapshot]
