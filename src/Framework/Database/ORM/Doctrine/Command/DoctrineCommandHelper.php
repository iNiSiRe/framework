<?php

namespace Framework\Database\ORM\Doctrine\Command;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;

/**
 * Provides some helper and convenience methods to configure doctrine commands in the context of bundles
 * and multiple connections/entity managers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class DoctrineCommandHelper
{
    /**
     * Convenience method to push the helper sets of a given entity manager into the application.
     *
     * @param Application $application
     * @param string      $emName
     */
    public static function setApplicationEntityManager(Application $application, $emName)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $application->getHelperSet()->get('container')->getContainer()->get('doctrine')->getManager();
        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');
    }
}