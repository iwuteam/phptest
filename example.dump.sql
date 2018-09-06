/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `companyPost` varchar(255) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `isEmailConfirmed` tinyint(1) DEFAULT NULL,
  `isAdmin` tinyint(1) NOT NULL,
  `avatar` int(11) DEFAULT NULL,
  `thumbnailPattern` varchar(255) DEFAULT NULL,
  `encryptedPassword` varchar(255) DEFAULT NULL,
  `isInvited` tinyint(1) DEFAULT NULL,  
  `lastActivityAt` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `isDeleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;

INSERT INTO `user` (`email`, `role`, `companyPost`, `company`, `name`, `surname`, `phone`, `isEmailConfirmed`, `isAdmin`, `avatar`, `thumbnailPattern`, `encryptedPassword`, `isInvited`, `id`, `lastActivityAt`, `createdAt`, `updatedAt`, `isDeleted`)
VALUES
	('4232456@mail.ru','employee','г. Москва',3,'Александр','Измайлов','79097503375',1,1,8,NULL,'$2a$10$S//os/QbQjpWFCxTLZwapeUAYkjNBsI6YKI/CUWUjqKj/7U2g.Ddm',0,3,'2018-09-05 18:03:49','2017-04-18 11:10:16','2018-09-05 18:03:49',0),
	('asmaicheva@gmail.com','employee','Директор',4,'Генадий','Павлов','78589765365',1,1,NULL,NULL,'$2a$10$QD6i3MEueIN5D1v1o8LIFeuuxkSMladfa5yupNECA0pPE6KDXSTkC',0,4,'2018-05-24 10:07:27','2017-04-18 11:37:32','2018-05-24 10:07:27',0),
	('qrat25@mail.ru','private','',529,'Стас','Осипов','79089912169',0,1,NULL,NULL,'$2a$10$/RKSejjncXjOY0utX4TKPe.4imgcPg2G0CShSAbTvPet7M2.6Y.7K',0,558,'2018-08-24 13:25:40','2018-08-24 13:24:58','2018-08-24 13:25:40',0),
	('amminkons@sberbank.ru','employee',NULL,530,'Андрей','Владимиров','79163983751',1,0,NULL,NULL,'$2a$10$WidsNeJ/3vga9AP6mNBUgu7ssUbNk31bNN8hdiV/LYDLN46nsaQc6',0,559,'2018-08-31 12:45:09','2018-08-31 06:39:40','2018-08-31 12:45:09',0);

/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
