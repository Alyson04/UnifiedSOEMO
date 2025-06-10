-- First, verify if the entry already exists
SELECT * FROM organization_members WHERE user_id = 6 AND organization_id = 10;

-- If no rows are returned from the above query, run this insert:
INSERT INTO organization_members (user_id, organization_id) 
VALUES (6, 10);

-- Verify the insertion
SELECT om.*, nu.firstName, nu.lastName, no.name as org_name
FROM organization_members om
JOIN newusers nu ON om.user_id = nu.id
JOIN neworganizations no ON om.organization_id = no.id
WHERE om.user_id = 6; 