<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use DirectoryIterator;
use SplFileInfo;
use Symfony\Component\DependencyInjection\Container;

class EntityLoader
{

    private const ENTITY_PATH = 'DatabaseManagement' . DIRECTORY_SEPARATOR . 'Entity';
    private string $entityPath = 'DatabaseManagement/src/Entity';

    public function __construct(private readonly Container $container,
                                private readonly string $projectRoot)
    {
        $this->entityPath = $this->projectRoot . '/' . $this->entityPath;
    }

    public function registerEntityRepositories()
    {
        foreach ($this->getEntityDefinitionClassesInDirectory($this->entityPath) as $entityDefinitionClass) {
            $entityDefinition = new $entityDefinitionClass();
            if(!($entityDefinition instanceof EntityDefinition)) continue;

            $this->container->register($entityDefinition->getEntityName() . '.definition', $entityDefinitionClass);

            $entityRepository = new EntityRepository(
                $this->container->get($entityDefinition->getEntityName() . '.definition'),
                $this->container->get(Connection::class)
            );

            $this->container->set($entityDefinition->getEntityName() . '.repository', $entityRepository);
        }
    }

    private function getEntityDefinitionClassesInDirectory($directory)
    {
        $classes = [];

        // Iterate through the directory
        $iterator = new DirectoryIterator($directory);
        foreach ($iterator as $path) {
            if(!($path instanceof SplFileInfo)) continue;
            if($path->getFilename() === '.' || $path->getFilename() === '..') continue;
            $directoryIterator = new DirectoryIterator($path->getRealPath());

            $entityFolderName = $path->getFilename();

            foreach ($directoryIterator as $file) {
                if(!($file instanceof SplFileInfo)) continue;
                if($file->isFile() && $file->getExtension() === 'php') {
                    $classPath = self::ENTITY_PATH . DIRECTORY_SEPARATOR . $entityFolderName . DIRECTORY_SEPARATOR . $file->getFileInfo()->getFilename();
                    $classPath = str_replace('.php', '', $classPath);

                    if(!str_ends_with($classPath, 'Definition')) continue;

                    if(!class_exists($classPath)) continue;

                    $classes[] = $classPath;
                }
            }
        }

        return $classes;
    }

}