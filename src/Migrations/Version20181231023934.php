<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181231023934 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE driver DROP salaire, DROP period_of_travel, DROP salaire_per_day');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B1BE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
        $this->addSql('CREATE INDEX IDX_F2145B1BE6CAE90 ON fuel_reconciliation (mission_id)');
        $this->addSql('ALTER TABLE mission ADD salaire NUMERIC(50, 2) NOT NULL, ADD period_of_work INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE driver ADD salaire NUMERIC(50, 2) NOT NULL, ADD period_of_travel SMALLINT NOT NULL, ADD salaire_per_day NUMERIC(50, 2) NOT NULL');
        $this->addSql('ALTER TABLE fuel_reconciliation DROP FOREIGN KEY FK_F2145B1BE6CAE90');
        $this->addSql('DROP INDEX IDX_F2145B1BE6CAE90 ON fuel_reconciliation');
        $this->addSql('ALTER TABLE mission DROP salaire, DROP period_of_work');
    }
}
