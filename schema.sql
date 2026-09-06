
CREATE TABLE IF NOT EXISTS `albuns` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_albuns_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `midias` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `album_id` INT UNSIGNED NOT NULL,
    `nome_original` VARCHAR(255) NOT NULL,
    `nome_arquivo` VARCHAR(255) NOT NULL,
    `caminho` VARCHAR(500) NOT NULL,
    `tipo` ENUM('image', 'video') NOT NULL,
    `is_public` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_midias_nome_arquivo` (`nome_arquivo`),
    KEY `idx_midias_album_public` (`album_id`, `is_public`),
    CONSTRAINT `fk_midias_albuns` FOREIGN KEY (`album_id`) 
        REFERENCES `albuns` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;