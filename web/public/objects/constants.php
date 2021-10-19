<?php
const ERROR_CORRECT_FIELDS = 1;
const ERROR_LOAD_AVATAR = 2;

const SELECT_USER = 'SELECT * FROM `test` WHERE `login` = :login AND `password` = :password';
const SELECT_LOGIN = 'SELECT * FROM test WHERE login = :login';
const INSERT_USER = 'INSERT INTO test (id, fullName, login, email, password, avatar) VALUES (NULL, :fullName, :login, :email, :password, :path)';