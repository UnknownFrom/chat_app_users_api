ALTER TABLE `users`
    ADD `email`   VARCHAR(255) NOT NULL,
    ADD `confirm` BOOLEAN      NOT NULL;