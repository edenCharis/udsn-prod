-- ============================================================
-- Auto-sync notation from ligne1 / ligne2
-- No stored procedure — logic inlined in each trigger
-- to avoid mysql.proc version mismatch on MariaDB.
-- ============================================================

-- ── Drop existing triggers ───────────────────────────────────
DROP TRIGGER IF EXISTS ligne1_after_insert;
DROP TRIGGER IF EXISTS ligne1_after_update;
DROP TRIGGER IF EXISTS ligne1_after_delete;
DROP TRIGGER IF EXISTS ligne2_after_insert;
DROP TRIGGER IF EXISTS ligne2_after_update;
DROP TRIGGER IF EXISTS ligne2_after_delete;

-- ── Also drop old procedure if it exists ─────────────────────
DROP PROCEDURE IF EXISTS sync_notation_ecue;

DELIMITER //

-- ============================================================
-- ligne1 — INSERT
-- ============================================================
CREATE TRIGGER ligne1_after_insert
AFTER INSERT ON ligne1
FOR EACH ROW
BEGIN
    DECLARE v_etudiant      INT     DEFAULT NULL;
    DECLARE v_moy_ex        DOUBLE  DEFAULT NULL;
    DECLARE v_moy_ratt      DOUBLE  DEFAULT NULL;
    DECLARE v_moy_dev       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen_ratt  DOUBLE  DEFAULT NULL;
    DECLARE v_notation_id   INT     DEFAULT NULL;
    DECLARE v_classe        VARCHAR(300) DEFAULT NULL;
    DECLARE v_ecue_lib      VARCHAR(300) DEFAULT NULL;

    SELECT a.etudiant INTO v_etudiant FROM anonymat a
    WHERE a.numero=NEW.anonymat AND a.code_ecue=NEW.code_ecue
      AND a.annee=NEW.annee AND a.semestre=NEW.semestre
      AND a.etab=NEW.etab AND a.type=NEW.type_examen LIMIT 1;

    IF v_etudiant IS NOT NULL THEN
        SELECT ROUND(AVG(l.note),2) INTO v_moy_ex FROM ligne1 l
        JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
        WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session Ordinaire' AND a.etudiant=v_etudiant;

        SELECT ROUND(AVG(l.note),2) INTO v_moy_ratt FROM ligne1 l
        JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
        WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session de Rappel' AND a.etudiant=v_etudiant;

        SELECT ROUND(AVG(note),2) INTO v_moy_dev FROM ligne2
        WHERE etudiant=v_etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab;

        IF v_moy_dev IS NOT NULL AND v_moy_ex   IS NOT NULL THEN SET v_moy_gen      = ROUND((v_moy_dev+v_moy_ex)  /2,2); END IF;
        IF v_moy_dev IS NOT NULL AND v_moy_ratt IS NOT NULL THEN SET v_moy_gen_ratt = ROUND((v_moy_dev+v_moy_ratt)/2,2); END IF;

        SELECT id INTO v_notation_id FROM notation
        WHERE inscription=v_etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab LIMIT 1;

        IF v_notation_id IS NOT NULL THEN
            UPDATE notation SET moyDev=v_moy_dev, moyEx=v_moy_ex, session_rappel=v_moy_ratt, moyGen=v_moy_gen, moyenGenRattrapage=v_moy_gen_ratt WHERE id=v_notation_id;
        ELSEIF v_moy_dev IS NOT NULL OR v_moy_ex IS NOT NULL OR v_moy_ratt IS NOT NULL THEN
            SELECT classe INTO v_classe FROM inscription WHERE id=v_etudiant LIMIT 1;
            SELECT libelle INTO v_ecue_lib FROM ecue WHERE code_ecue=NEW.code_ecue AND etab=NEW.etab LIMIT 1;
            INSERT INTO notation (inscription,classe,ecue,code_ecue,annee,moyDev,moyEx,session_rappel,moyGen,moyenGenRattrapage,semestre,etab)
            VALUES (v_etudiant,v_classe,COALESCE(v_ecue_lib,NEW.code_ecue),NEW.code_ecue,NEW.annee,v_moy_dev,v_moy_ex,v_moy_ratt,v_moy_gen,v_moy_gen_ratt,NEW.semestre,NEW.etab);
        END IF;
    END IF;
END//

-- ============================================================
-- ligne1 — UPDATE
-- ============================================================
CREATE TRIGGER ligne1_after_update
AFTER UPDATE ON ligne1
FOR EACH ROW
BEGIN
    DECLARE v_etudiant      INT     DEFAULT NULL;
    DECLARE v_moy_ex        DOUBLE  DEFAULT NULL;
    DECLARE v_moy_ratt      DOUBLE  DEFAULT NULL;
    DECLARE v_moy_dev       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen_ratt  DOUBLE  DEFAULT NULL;
    DECLARE v_notation_id   INT     DEFAULT NULL;
    DECLARE v_classe        VARCHAR(300) DEFAULT NULL;
    DECLARE v_ecue_lib      VARCHAR(300) DEFAULT NULL;

    SELECT a.etudiant INTO v_etudiant FROM anonymat a
    WHERE a.numero=NEW.anonymat AND a.code_ecue=NEW.code_ecue
      AND a.annee=NEW.annee AND a.semestre=NEW.semestre
      AND a.etab=NEW.etab AND a.type=NEW.type_examen LIMIT 1;

    IF v_etudiant IS NOT NULL THEN
        SELECT ROUND(AVG(l.note),2) INTO v_moy_ex FROM ligne1 l
        JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
        WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session Ordinaire' AND a.etudiant=v_etudiant;

        SELECT ROUND(AVG(l.note),2) INTO v_moy_ratt FROM ligne1 l
        JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
        WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session de Rappel' AND a.etudiant=v_etudiant;

        SELECT ROUND(AVG(note),2) INTO v_moy_dev FROM ligne2
        WHERE etudiant=v_etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab;

        IF v_moy_dev IS NOT NULL AND v_moy_ex   IS NOT NULL THEN SET v_moy_gen      = ROUND((v_moy_dev+v_moy_ex)  /2,2); END IF;
        IF v_moy_dev IS NOT NULL AND v_moy_ratt IS NOT NULL THEN SET v_moy_gen_ratt = ROUND((v_moy_dev+v_moy_ratt)/2,2); END IF;

        SELECT id INTO v_notation_id FROM notation
        WHERE inscription=v_etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab LIMIT 1;

        IF v_notation_id IS NOT NULL THEN
            UPDATE notation SET moyDev=v_moy_dev, moyEx=v_moy_ex, session_rappel=v_moy_ratt, moyGen=v_moy_gen, moyenGenRattrapage=v_moy_gen_ratt WHERE id=v_notation_id;
        ELSEIF v_moy_dev IS NOT NULL OR v_moy_ex IS NOT NULL OR v_moy_ratt IS NOT NULL THEN
            SELECT classe INTO v_classe FROM inscription WHERE id=v_etudiant LIMIT 1;
            SELECT libelle INTO v_ecue_lib FROM ecue WHERE code_ecue=NEW.code_ecue AND etab=NEW.etab LIMIT 1;
            INSERT INTO notation (inscription,classe,ecue,code_ecue,annee,moyDev,moyEx,session_rappel,moyGen,moyenGenRattrapage,semestre,etab)
            VALUES (v_etudiant,v_classe,COALESCE(v_ecue_lib,NEW.code_ecue),NEW.code_ecue,NEW.annee,v_moy_dev,v_moy_ex,v_moy_ratt,v_moy_gen,v_moy_gen_ratt,NEW.semestre,NEW.etab);
        END IF;
    END IF;
END//

-- ============================================================
-- ligne1 — DELETE
-- ============================================================
CREATE TRIGGER ligne1_after_delete
AFTER DELETE ON ligne1
FOR EACH ROW
BEGIN
    DECLARE v_etudiant      INT     DEFAULT NULL;
    DECLARE v_moy_ex        DOUBLE  DEFAULT NULL;
    DECLARE v_moy_ratt      DOUBLE  DEFAULT NULL;
    DECLARE v_moy_dev       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen_ratt  DOUBLE  DEFAULT NULL;
    DECLARE v_notation_id   INT     DEFAULT NULL;

    SELECT a.etudiant INTO v_etudiant FROM anonymat a
    WHERE a.numero=OLD.anonymat AND a.code_ecue=OLD.code_ecue
      AND a.annee=OLD.annee AND a.semestre=OLD.semestre
      AND a.etab=OLD.etab AND a.type=OLD.type_examen LIMIT 1;

    IF v_etudiant IS NOT NULL THEN
        SELECT ROUND(AVG(l.note),2) INTO v_moy_ex FROM ligne1 l
        JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
        WHERE l.code_ecue=OLD.code_ecue AND l.semestre=OLD.semestre AND l.annee=OLD.annee AND l.etab=OLD.etab AND l.type_examen='Session Ordinaire' AND a.etudiant=v_etudiant;

        SELECT ROUND(AVG(l.note),2) INTO v_moy_ratt FROM ligne1 l
        JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
        WHERE l.code_ecue=OLD.code_ecue AND l.semestre=OLD.semestre AND l.annee=OLD.annee AND l.etab=OLD.etab AND l.type_examen='Session de Rappel' AND a.etudiant=v_etudiant;

        SELECT ROUND(AVG(note),2) INTO v_moy_dev FROM ligne2
        WHERE etudiant=v_etudiant AND code_ecue=OLD.code_ecue AND semestre=OLD.semestre AND annee=OLD.annee AND etab=OLD.etab;

        IF v_moy_dev IS NOT NULL AND v_moy_ex   IS NOT NULL THEN SET v_moy_gen      = ROUND((v_moy_dev+v_moy_ex)  /2,2); END IF;
        IF v_moy_dev IS NOT NULL AND v_moy_ratt IS NOT NULL THEN SET v_moy_gen_ratt = ROUND((v_moy_dev+v_moy_ratt)/2,2); END IF;

        SELECT id INTO v_notation_id FROM notation
        WHERE inscription=v_etudiant AND code_ecue=OLD.code_ecue AND semestre=OLD.semestre AND annee=OLD.annee AND etab=OLD.etab LIMIT 1;

        IF v_notation_id IS NOT NULL THEN
            UPDATE notation SET moyDev=v_moy_dev, moyEx=v_moy_ex, session_rappel=v_moy_ratt, moyGen=v_moy_gen, moyenGenRattrapage=v_moy_gen_ratt WHERE id=v_notation_id;
        END IF;
    END IF;
END//

-- ============================================================
-- ligne2 — INSERT
-- ============================================================
CREATE TRIGGER ligne2_after_insert
AFTER INSERT ON ligne2
FOR EACH ROW
BEGIN
    DECLARE v_moy_ex        DOUBLE  DEFAULT NULL;
    DECLARE v_moy_ratt      DOUBLE  DEFAULT NULL;
    DECLARE v_moy_dev       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen_ratt  DOUBLE  DEFAULT NULL;
    DECLARE v_notation_id   INT     DEFAULT NULL;
    DECLARE v_classe        VARCHAR(300) DEFAULT NULL;
    DECLARE v_ecue_lib      VARCHAR(300) DEFAULT NULL;

    SELECT ROUND(AVG(l.note),2) INTO v_moy_ex FROM ligne1 l
    JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
    WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session Ordinaire' AND a.etudiant=NEW.etudiant;

    SELECT ROUND(AVG(l.note),2) INTO v_moy_ratt FROM ligne1 l
    JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
    WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session de Rappel' AND a.etudiant=NEW.etudiant;

    SELECT ROUND(AVG(note),2) INTO v_moy_dev FROM ligne2
    WHERE etudiant=NEW.etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab;

    IF v_moy_dev IS NOT NULL AND v_moy_ex   IS NOT NULL THEN SET v_moy_gen      = ROUND((v_moy_dev+v_moy_ex)  /2,2); END IF;
    IF v_moy_dev IS NOT NULL AND v_moy_ratt IS NOT NULL THEN SET v_moy_gen_ratt = ROUND((v_moy_dev+v_moy_ratt)/2,2); END IF;

    SELECT id INTO v_notation_id FROM notation
    WHERE inscription=NEW.etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab LIMIT 1;

    IF v_notation_id IS NOT NULL THEN
        UPDATE notation SET moyDev=v_moy_dev, moyEx=v_moy_ex, session_rappel=v_moy_ratt, moyGen=v_moy_gen, moyenGenRattrapage=v_moy_gen_ratt WHERE id=v_notation_id;
    ELSEIF v_moy_dev IS NOT NULL OR v_moy_ex IS NOT NULL OR v_moy_ratt IS NOT NULL THEN
        SELECT classe INTO v_classe FROM inscription WHERE id=NEW.etudiant LIMIT 1;
        SELECT libelle INTO v_ecue_lib FROM ecue WHERE code_ecue=NEW.code_ecue AND etab=NEW.etab LIMIT 1;
        INSERT INTO notation (inscription,classe,ecue,code_ecue,annee,moyDev,moyEx,session_rappel,moyGen,moyenGenRattrapage,semestre,etab)
        VALUES (NEW.etudiant,v_classe,COALESCE(v_ecue_lib,NEW.code_ecue),NEW.code_ecue,NEW.annee,v_moy_dev,v_moy_ex,v_moy_ratt,v_moy_gen,v_moy_gen_ratt,NEW.semestre,NEW.etab);
    END IF;
END//

-- ============================================================
-- ligne2 — UPDATE
-- ============================================================
CREATE TRIGGER ligne2_after_update
AFTER UPDATE ON ligne2
FOR EACH ROW
BEGIN
    DECLARE v_moy_ex        DOUBLE  DEFAULT NULL;
    DECLARE v_moy_ratt      DOUBLE  DEFAULT NULL;
    DECLARE v_moy_dev       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen_ratt  DOUBLE  DEFAULT NULL;
    DECLARE v_notation_id   INT     DEFAULT NULL;
    DECLARE v_classe        VARCHAR(300) DEFAULT NULL;
    DECLARE v_ecue_lib      VARCHAR(300) DEFAULT NULL;

    SELECT ROUND(AVG(l.note),2) INTO v_moy_ex FROM ligne1 l
    JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
    WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session Ordinaire' AND a.etudiant=NEW.etudiant;

    SELECT ROUND(AVG(l.note),2) INTO v_moy_ratt FROM ligne1 l
    JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
    WHERE l.code_ecue=NEW.code_ecue AND l.semestre=NEW.semestre AND l.annee=NEW.annee AND l.etab=NEW.etab AND l.type_examen='Session de Rappel' AND a.etudiant=NEW.etudiant;

    SELECT ROUND(AVG(note),2) INTO v_moy_dev FROM ligne2
    WHERE etudiant=NEW.etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab;

    IF v_moy_dev IS NOT NULL AND v_moy_ex   IS NOT NULL THEN SET v_moy_gen      = ROUND((v_moy_dev+v_moy_ex)  /2,2); END IF;
    IF v_moy_dev IS NOT NULL AND v_moy_ratt IS NOT NULL THEN SET v_moy_gen_ratt = ROUND((v_moy_dev+v_moy_ratt)/2,2); END IF;

    SELECT id INTO v_notation_id FROM notation
    WHERE inscription=NEW.etudiant AND code_ecue=NEW.code_ecue AND semestre=NEW.semestre AND annee=NEW.annee AND etab=NEW.etab LIMIT 1;

    IF v_notation_id IS NOT NULL THEN
        UPDATE notation SET moyDev=v_moy_dev, moyEx=v_moy_ex, session_rappel=v_moy_ratt, moyGen=v_moy_gen, moyenGenRattrapage=v_moy_gen_ratt WHERE id=v_notation_id;
    ELSEIF v_moy_dev IS NOT NULL OR v_moy_ex IS NOT NULL OR v_moy_ratt IS NOT NULL THEN
        SELECT classe INTO v_classe FROM inscription WHERE id=NEW.etudiant LIMIT 1;
        SELECT libelle INTO v_ecue_lib FROM ecue WHERE code_ecue=NEW.code_ecue AND etab=NEW.etab LIMIT 1;
        INSERT INTO notation (inscription,classe,ecue,code_ecue,annee,moyDev,moyEx,session_rappel,moyGen,moyenGenRattrapage,semestre,etab)
        VALUES (NEW.etudiant,v_classe,COALESCE(v_ecue_lib,NEW.code_ecue),NEW.code_ecue,NEW.annee,v_moy_dev,v_moy_ex,v_moy_ratt,v_moy_gen,v_moy_gen_ratt,NEW.semestre,NEW.etab);
    END IF;
END//

-- ============================================================
-- ligne2 — DELETE
-- ============================================================
CREATE TRIGGER ligne2_after_delete
AFTER DELETE ON ligne2
FOR EACH ROW
BEGIN
    DECLARE v_moy_ex        DOUBLE  DEFAULT NULL;
    DECLARE v_moy_ratt      DOUBLE  DEFAULT NULL;
    DECLARE v_moy_dev       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen       DOUBLE  DEFAULT NULL;
    DECLARE v_moy_gen_ratt  DOUBLE  DEFAULT NULL;
    DECLARE v_notation_id   INT     DEFAULT NULL;

    SELECT ROUND(AVG(l.note),2) INTO v_moy_ex FROM ligne1 l
    JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
    WHERE l.code_ecue=OLD.code_ecue AND l.semestre=OLD.semestre AND l.annee=OLD.annee AND l.etab=OLD.etab AND l.type_examen='Session Ordinaire' AND a.etudiant=OLD.etudiant;

    SELECT ROUND(AVG(l.note),2) INTO v_moy_ratt FROM ligne1 l
    JOIN anonymat a ON a.numero=l.anonymat AND a.code_ecue=l.code_ecue AND a.annee=l.annee AND a.semestre=l.semestre AND a.etab=l.etab AND a.type=l.type_examen
    WHERE l.code_ecue=OLD.code_ecue AND l.semestre=OLD.semestre AND l.annee=OLD.annee AND l.etab=OLD.etab AND l.type_examen='Session de Rappel' AND a.etudiant=OLD.etudiant;

    SELECT ROUND(AVG(note),2) INTO v_moy_dev FROM ligne2
    WHERE etudiant=OLD.etudiant AND code_ecue=OLD.code_ecue AND semestre=OLD.semestre AND annee=OLD.annee AND etab=OLD.etab;

    IF v_moy_dev IS NOT NULL AND v_moy_ex   IS NOT NULL THEN SET v_moy_gen      = ROUND((v_moy_dev+v_moy_ex)  /2,2); END IF;
    IF v_moy_dev IS NOT NULL AND v_moy_ratt IS NOT NULL THEN SET v_moy_gen_ratt = ROUND((v_moy_dev+v_moy_ratt)/2,2); END IF;

    SELECT id INTO v_notation_id FROM notation
    WHERE inscription=OLD.etudiant AND code_ecue=OLD.code_ecue AND semestre=OLD.semestre AND annee=OLD.annee AND etab=OLD.etab LIMIT 1;

    IF v_notation_id IS NOT NULL THEN
        UPDATE notation SET moyDev=v_moy_dev, moyEx=v_moy_ex, session_rappel=v_moy_ratt, moyGen=v_moy_gen, moyenGenRattrapage=v_moy_gen_ratt WHERE id=v_notation_id;
    END IF;
END//

DELIMITER ;
