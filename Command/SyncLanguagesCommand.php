<?php

namespace Modera\LanguagesBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Modera\LanguagesBundle\DependencyInjection\ModeraLanguagesExtension;
use Modera\LanguagesBundle\Entity\Language;

/**
 * From config to database.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SyncLanguagesCommand extends Command
{
    private EntityManagerInterface $em;

    private ParameterBagInterface $params;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->params = $params;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('modera:languages:config-sync')
            ->setDescription('Synchronize languages config with database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = $this->getConfigLanguages();
        $dbLanguages = $this->em->getRepository(Language::clazz())->findAll();

        $updated = array();
        $tableRows = array();
        if (count($dbLanguages)) {
            /* @var Language $dbLanguage */
            foreach ($dbLanguages as $dbLanguage) {
                $language = null;
                foreach ($languages as $_language) {
                    if ($_language['locale'] == $dbLanguage->getLocale()) {
                        $language = $_language;
                        break;
                    }
                }

                if (is_array($language)) {
                    $updated[] = $language['locale'];
                    $dbLanguage->setEnabled($language['is_enabled'] ? true : false);
                } else {
                    $dbLanguage->setEnabled(false);
                }
                $this->em->persist($dbLanguage);
                $tableRows[] = $this->tableRow($dbLanguage);
            }
        }

        foreach ($languages as $language) {
            if (!in_array($language['locale'], $updated)) {
                $dbLanguage = new Language();
                $dbLanguage->setLocale($language['locale']);
                $dbLanguage->setEnabled($language['is_enabled'] ? true : false);
                $this->em->persist($dbLanguage);
                $tableRows[] = $this->tableRow($dbLanguage);
            }
        }

        $this->em->flush();
        
        $table = new Table($output);
        $table->setHeaders(array('locale', 'name', 'enabled'));
        $table->setRows($tableRows);
        $table->render($output);

        return 0;
    }

    /**
     * @return array
     */
    protected function getConfigLanguages()
    {
        return $this->params->get(ModeraLanguagesExtension::CONFIG_KEY);
    }

    /**
     * @param Language $dbLanguage
     *
     * @return array
     */
    private function tableRow(Language $dbLanguage)
    {
        return array(
            $dbLanguage->getLocale(),
            $dbLanguage->getName(),
            $dbLanguage->isEnabled(),
        );
    }
}
