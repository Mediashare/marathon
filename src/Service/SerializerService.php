<?php
namespace Mediashare\Marathon\Service;

use Mediashare\Marathon\Entity\Config;
use Mediashare\Marathon\Entity\Task;
use Mediashare\Marathon\Exception\FileNotFoundException;
use Mediashare\Marathon\Exception\JsonDecodeException;
use Mediashare\Marathon\Trait\ArrayToEntityTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerService {
    use ArrayToEntityTrait;

    private Serializer $serializer;

    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
        $this->serializer = new Serializer([
            new ObjectNormalizer(propertyTypeExtractor: new ReflectionExtractor()),
            new ArrayDenormalizer(),
        ], [new JsonEncoder()]);
    }

    /**
     * Convert json file to Entity object
     * @throws FileNotFoundException
     * @throws JsonDecodeException
     */
    public function read(string $filepath, string $className): Task|Config {
        if (!$this->filesystem->exists($filepath)):
            throw new FileNotFoundException($filepath, $className);
        endif;

        $content = file_get_contents($filepath);

        if (!($taskArray = json_decode($content, true)) || json_last_error() !== JSON_ERROR_NONE):
            throw new JsonDecodeException($filepath, $className);
        endif;

        return $this->arrayToEntity($taskArray, $className);
    }

    /**
     * Write task file
     */
    public function writeTask(string $filepath, Task $task): self {
        $this
            ->filesystem
            ->dumpFile(
                $filepath,
                $this->serializer->serialize($task, 'json')
            )
        ;

        return $this;
    }
}
