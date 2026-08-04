-- UCID: mrc82
-- Date: 2026-08-02
-- Summary: Creates the Characters table for API-imported and manually created
-- Rick and Morty character records.

CREATE TABLE IF NOT EXISTS Characters (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    api_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    status VARCHAR(30) NOT NULL,
    species VARCHAR(100) NOT NULL,
    gender VARCHAR(30) NOT NULL,
    origin_name VARCHAR(255) NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NULL,
    is_api TINYINT(1) NOT NULL DEFAULT 0,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_characters_api_id (api_id),
    INDEX idx_characters_name (name),
    INDEX idx_characters_status (status),
    INDEX idx_characters_species (species),
    INDEX idx_characters_source (is_api)
);