--
-- Table structure for FW_Preferences
--
DROP TABLE IF EXISTS FW_Preferences;
CREATE TABLE FW_Preferences (
    dbid INT(11) DEFAULT NULL,
    name VARCHAR(80) DEFAULT NULL,
    type VARCHAR(10) NOT NULL DEFAULT "string",
    string_value VARCHAR(255) DEFAULT NULL,
    bool_value INT(1) DEFAULT 0,
    int_value INT(11) DEFAULT 0,
    float_value DECIMAL(10,4) DEFAULT 0,
    array_value LONGTEXT DEFAULT NULL,
    text_value LONGTEXT DEFAULT NULL,
    PRIMARY KEY (name)
)TYPE=InnoDB;

--
-- Table structure for FW_UploadedFiles
-- Le nom de la table dépend de la constante DB_UPLOAD_TABLE
--
DROP TABLE IF EXISTS FW_UploadedFiles;
CREATE TABLE FW_UploadedFiles (
    _DBId INT(11) DEFAULT NULL,
    _DataB64 LONGTEXT DEFAULT NULL,
    _DataBLOB LONGBLOB DEFAULT NULL,
    _Name varchar(255) DEFAULT NULL,
    _UUID varchar(255) DEFAULT NULL,
    _MimeType varchar(255) DEFAULT NULL,
    PRIMARY KEY (_Name)
)TYPE=InnoDB;
