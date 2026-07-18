INSERT INTO Roles (id, name, description, is_active)
VALUES (-1, 'Admin', 'Can access project administration pages.', 1)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    is_active =  VALUES(is_active);