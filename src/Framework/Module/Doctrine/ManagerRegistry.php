<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 17.06.15
 * Time: 15:57
 */

namespace Framework\Module\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\ORM\EntityManager;

class ManagerRegistry implements ManagerRegistryInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets the default connection name.
     *
     * @return string The default connection name.
     */
    public function getDefaultConnectionName()
    {
        return 'default';
    }

    /**
     * Gets the named connection.
     *
     * @param string $name The connection name (null for the default one).
     *
     * @return object
     */
    public function getConnection($name = null)
    {
        $this->em->getConnection();
    }

    /**
     * Gets an array of all registered connections.
     *
     * @return array An array of Connection instances.
     */
    public function getConnections()
    {
        return [$this->em->getConnection()];
    }

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names.
     */
    public function getConnectionNames()
    {
        return ['default'];
    }

    /**
     * Gets the default object manager name.
     *
     * @return string The default object manager name.
     */
    public function getDefaultManagerName()
    {
        return 'default';
    }

    /**
     * Gets a named object manager.
     *
     * @param string $name The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getManager($name = null)
    {
        return $this->em;
    }

    /**
     * Gets an array of all registered object managers.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager[] An array of ObjectManager instances
     */
    public function getManagers()
    {
        return [$this->em];
    }

    /**
     * Resets a named object manager.
     *
     * This method is useful when an object manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     *
     * Be warned that you will get a brand new object manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this object manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @param string|null $name The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     *
     * @throws \Exception
     */
    public function resetManager($name = null)
    {
        throw new \Exception('Cant reset manager');
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * This method looks for the alias in all registered object managers.
     *
     * @param string $alias The alias.
     *
     * @return string The full namespace.
     * @throws \Exception
     */
    public function getAliasNamespace($alias)
    {
        throw new \Exception('No alias');
    }

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names.
     */
    public function getManagerNames()
    {
        return ['default'];
    }

    /**
     * Gets the ObjectRepository for an persistent object.
     *
     * @param string $persistentObject      The name of the persistent object.
     * @param string $persistentManagerName The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->em->getRepository($persistentObject);
    }

    /**
     * Gets the object manager associated with a given class.
     *
     * @param string $class A persistent object class name.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    public function getManagerForClass($class)
    {
        return $this->em;
    }
}