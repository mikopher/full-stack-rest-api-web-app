ALTER TABLE Users
ADD COLUMN username VARCHAR(30) NOT NULL
DEFAULT (
    LEFT(
        CONCAT(
            SUBSTRING_INDEX(email, '@', 1),
            '-',
            MD5(email)
        ),
        30
    )
)
COMMENT 'Username defaults to email local part plus hash truncated to 30 characters'
AFTER email,
ADD UNIQUE KEY uq_users_username (username);