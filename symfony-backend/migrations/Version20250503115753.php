<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503115753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ejercicio (id SERIAL NOT NULL, nombre VARCHAR(255) NOT NULL, descripcion VARCHAR(1000) NOT NULL, dificultad VARCHAR(50) NOT NULL, categoria VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE entrenador (id SERIAL NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, especialidad VARCHAR(255) DEFAULT NULL, clientes_activos INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_FD19603BE7927C74 ON entrenador (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resultado_entreno (id SERIAL NOT NULL, usuario_id INT NOT NULL, rutina_id INT NOT NULL, fecha TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duracion_minutos INT DEFAULT NULL, dificultad_percibida INT DEFAULT NULL, comentarios VARCHAR(1000) DEFAULT NULL, completado BOOLEAN DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F86619FDDB38439E ON resultado_entreno (usuario_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F86619FDD7A88FCB ON resultado_entreno (rutina_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rutina (id SERIAL NOT NULL, entrenador_id INT NOT NULL, usuario_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, tipo_rutina VARCHAR(100) NOT NULL, series INT NOT NULL, categoria VARCHAR(100) NOT NULL, descripcion VARCHAR(1000) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A48AB2554FE90CDB ON rutina (entrenador_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A48AB255DB38439E ON rutina (usuario_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rutina_ejercicio (id SERIAL NOT NULL, rutina_id INT NOT NULL, ejercicio_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, series INT NOT NULL, repeticiones INT NOT NULL, descanso_segundos INT DEFAULT NULL, orden INT DEFAULT NULL, notas VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9081A591D7A88FCB ON rutina_ejercicio (rutina_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9081A59130890A7D ON rutina_ejercicio (ejercicio_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE usuario (id SERIAL NOT NULL, entrenador_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, genero VARCHAR(255) NOT NULL, altura NUMERIC(5, 2) DEFAULT NULL, peso_inicial NUMERIC(6, 2) DEFAULT NULL, lesiones VARCHAR(255) DEFAULT NULL, objetivo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2265B05D4FE90CDB ON usuario (entrenador_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultado_entreno ADD CONSTRAINT FK_F86619FDDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultado_entreno ADD CONSTRAINT FK_F86619FDD7A88FCB FOREIGN KEY (rutina_id) REFERENCES rutina (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina ADD CONSTRAINT FK_A48AB2554FE90CDB FOREIGN KEY (entrenador_id) REFERENCES entrenador (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina ADD CONSTRAINT FK_A48AB255DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina_ejercicio ADD CONSTRAINT FK_9081A591D7A88FCB FOREIGN KEY (rutina_id) REFERENCES rutina (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina_ejercicio ADD CONSTRAINT FK_9081A59130890A7D FOREIGN KEY (ejercicio_id) REFERENCES ejercicio (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE usuario ADD CONSTRAINT FK_2265B05D4FE90CDB FOREIGN KEY (entrenador_id) REFERENCES entrenador (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultado_entreno DROP CONSTRAINT FK_F86619FDDB38439E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultado_entreno DROP CONSTRAINT FK_F86619FDD7A88FCB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina DROP CONSTRAINT FK_A48AB2554FE90CDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina DROP CONSTRAINT FK_A48AB255DB38439E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina_ejercicio DROP CONSTRAINT FK_9081A591D7A88FCB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rutina_ejercicio DROP CONSTRAINT FK_9081A59130890A7D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE usuario DROP CONSTRAINT FK_2265B05D4FE90CDB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ejercicio
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE entrenador
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resultado_entreno
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rutina
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rutina_ejercicio
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE usuario
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
