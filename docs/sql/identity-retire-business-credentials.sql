-- ONE-WAY maintenance-window script for the BUSINESS database, MySQL >= 8.0.16.
-- Do not run before the frozen final Identity import, projection reconciliation,
-- and private identity_migration_control.authority activation have completed.
-- The operator must set @identity_authority_confirmed = 'identity' in this
-- connection after verifying those conditions. No production database is selected.
-- DDL commits implicitly. Stop all old account writers before beginning.
-- Application rollback MUST keep Identity authoritative and these guards intact.
-- A return to Legacy authority requires a separate frozen reverse migration;
-- dropping these guards or changing an environment flag is not a rollback.

DELIMITER $$
CREATE PROCEDURE identity_retire_business_credentials()
BEGIN
    IF COALESCE(@identity_authority_confirmed, '') <> 'identity' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Verify final Identity authority before retiring business credentials';
    END IF;
    UPDATE `user` SET `password_hash` = '!identity-owned', `auth_key` = '',
        `password_reset_token` = NULL, `verification_token` = NULL, `access_token` = NULL;
END$$
DELIMITER ;

CALL identity_retire_business_credentials();
DROP PROCEDURE identity_retire_business_credentials;

ALTER TABLE `user`
    MODIFY `password_hash` VARCHAR(255) NOT NULL DEFAULT '!identity-owned',
    MODIFY `auth_key` VARCHAR(32) NOT NULL DEFAULT '',
    ADD CONSTRAINT `chk_identity_business_credentials` CHECK (
        `password_hash` IS NOT NULL AND `password_hash` = '!identity-owned'
        AND `auth_key` IS NOT NULL AND `auth_key` = ''
        AND `password_reset_token` IS NULL
        AND `verification_token` IS NULL
        AND `access_token` IS NULL
    );

DELIMITER $$
CREATE TRIGGER identity_business_user_no_delete BEFORE DELETE ON `user`
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Identity business projections cannot be deleted; retain content ownership';
END$$

CREATE TRIGGER identity_business_user_stable_id BEFORE UPDATE ON `user`
FOR EACH ROW
BEGIN
    IF NEW.id <> OLD.id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Identity business projection IDs are immutable';
    END IF;
END$$
DELIMITER ;

-- Check guards after application. A disabled/deleted Identity subject is
-- projected with status=0; its stable user row and all business FKs remain.
SELECT COUNT(*) AS invalid_business_credentials FROM `user`
WHERE `password_hash` <> '!identity-owned' OR `auth_key` <> ''
    OR `password_reset_token` IS NOT NULL OR `verification_token` IS NOT NULL
    OR `access_token` IS NOT NULL;

-- Required privilege boundary (adapt account names; never grant app DDL):
-- 1. Business app: SELECT user / assignment / membership; normal business writes.
-- 2. Projection worker: INSERT/UPDATE user and projection state, INSERT user_info.
-- 3. Neither account may DELETE user, ALTER/DROP/TRIGGER/CREATE ROUTINE, or write
--    local provider bindings / roles / memberships. Remove broad database.* grants
--    before granting narrower privileges: a table REVOKE cannot undo a global grant.
-- TRUNCATE and table/trigger DDL bypass row triggers and therefore must be denied.
-- Refresh/scan manifests must already be imported; retire the old Redis/service
-- credentials and stop old workers separately. This SQL cannot secure Redis.
