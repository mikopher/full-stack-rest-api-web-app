-- UCID: mrc82
-- Date: 2026-08-06
-- Summary: Creates the UserCharacters relationship table so users can save
-- Rick and Morty characters without duplicating or deleting parent records.

CREATE TABLE IF NOT EXISTS UserCharacters (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    character_id INT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_usercharacters_user_character (
        user_id,
        character_id
    ),

    INDEX idx_usercharacters_user (user_id),
    INDEX idx_usercharacters_character (character_id),
    INDEX idx_usercharacters_active (is_active),

    CONSTRAINT fk_usercharacters_user
        FOREIGN KEY (user_id)
        REFERENCES Users (id)
        ON DELETE CASCADE,

    CONSTRAINT fk_usercharacters_character
        FOREIGN KEY (character_id)
        REFERENCES Characters (id)
        ON DELETE CASCADE
);