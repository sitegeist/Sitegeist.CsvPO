<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20190826144525 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        $this->addSql('CREATE INDEX source_index ON sitegeist_csvpo_domain_model_translationlabel (source(255))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3FDC4C55F8A7F73EA750E84180C698 ON sitegeist_csvpo_domain_model_translationlabel (source, label, locale)');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        $this->addSql('DROP INDEX source_index ON sitegeist_csvpo_domain_model_translationlabel');
        $this->addSql('DROP INDEX UNIQ_3FDC4C55F8A7F73EA750E84180C698 ON sitegeist_csvpo_domain_model_translationlabel');
    }
}
