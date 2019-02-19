INSERT INTO `server` (`server_id`, `title`, `description`, `ip`, `domain`, `secret`, `location`, `status`, `creation_date`, `last_updated`) VALUES 
(1, 'app1.hometradies.com', 'Hometradies App 1', '', 'app1.hometradies.com', 'f100d8c5c68684f4770ba66bf90be2c9', '', 1, '2013-11-19', NULL),
(2, 'app2.hometradies.com', 'Hometradies App 2', '', 'app2.hometradies.com', 'f100d8c5c68684f4770ba66bf90be2c8','', 1, '2013-11-19', NULL),
(3, 'app3.hometradies.com', 'Hometradies App 3', '', 'app3.hometradies.com', 'f100d8c5c68684f4770ba66bf90be2c7','', 1, '2013-11-19', NULL);

INSERT INTO  `application_server` (`application_id` , `server_id` , `creation_date` , `last_updated`) VALUES 
(1, 1, '2013-11-19', NULL),
(2, 2, '2013-11-19', NULL),
(1, 3, '2013-11-19', NULL);

