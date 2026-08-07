-- Normalize URLs after moving Udyam Ventures from /udyamventures or /demo
-- to the root of https://udyamventures.com.
-- Back up the database before running this migration.

START TRANSACTION;

-- Structured page and home data stored as JSON.
UPDATE page_sections
SET data = REPLACE(REPLACE(CAST(data AS CHAR), '/udyamventures/', '/'), '/demo/', '/')
WHERE CAST(data AS CHAR) LIKE '%/udyamventures/%'
   OR CAST(data AS CHAR) LIKE '%/demo/%';

UPDATE admin_records
SET data = REPLACE(REPLACE(CAST(data AS CHAR), '/udyamventures/', '/'), '/demo/', '/')
WHERE CAST(data AS CHAR) LIKE '%/udyamventures/%'
   OR CAST(data AS CHAR) LIKE '%/demo/%';

-- Page HTML may contain legacy image or link paths.
UPDATE pages
SET content = REPLACE(REPLACE(content, '/udyamventures/', '/'), '/demo/', '/')
WHERE content LIKE '%/udyamventures/%'
   OR content LIKE '%/demo/%';

-- Customer portal documents and notifications can contain public paths.
UPDATE customer_documents
SET file_path = REPLACE(REPLACE(file_path, '/udyamventures/', '/'), '/demo/', '/')
WHERE file_path LIKE '/udyamventures/%'
   OR file_path LIKE '/demo/%';

UPDATE customer_notifications
SET action_url = REPLACE(REPLACE(action_url, '/udyamventures/', '/'), '/demo/', '/')
WHERE action_url LIKE '/udyamventures/%'
   OR action_url LIKE '/demo/%';

COMMIT;
