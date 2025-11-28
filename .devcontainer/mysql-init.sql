CREATE DATABASE IF NOT EXISTS `yii2basic_test`;

-- Usuário com permissões
GRANT ALL PRIVILEGES ON yii2.* TO 'yii2'@'%';
GRANT ALL PRIVILEGES ON yii2basic_test.* TO 'yii2'@'%';
FLUSH PRIVILEGES;