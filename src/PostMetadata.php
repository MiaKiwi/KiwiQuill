<?php

namespace Miakiwi\Kiwiquill;

use Framework\Logger;
use MiaKiwi\Kaphpir\IData;
use Symfony\Component\Yaml\Yaml;



class PostMetadata implements IData
{
    public const MERGE = 'merge'; // Keyword for merging metadata.
    public const REPLACE = 'replace'; // Keyword for replacing metadata.

    /**
     * Metadata for the post.
     * @var array
     */
    private array $metadata = [];



    /**
     * Instantiate a new PostMetadata object.
     * @param array $metadata The metadata to initialize the object with.
     */
    public function __construct(array $metadata = [])
    {
        $this->clearMetadata();



        $this->setMetadata($metadata);
    }



    /**
     * Clear the metadata.
     * @return void
     */
    public function clearMetadata(): void
    {
        $this->metadata = [];
    }



    /**
     * Set the metadata for the post.
     * @param self|array|null $metadata The metadata to set for the post. Can be an array or another PostMetadata object.
     * @param string $Mode The mode to set the metadata. Can be 'merge' or 'replace'.
     * @return void
     */
    public function setMetadata(self|array|null $metadata, string $Mode = 'replace'): void
    {
        // If the metadata is null, clear the metadata.
        if ($metadata === null) {
            $this->clearMetadata();
            return;
        }



        // If the metadata is an instance of PostMetadata, convert it to an array.
        if ($metadata instanceof self) {
            $metadata = $metadata->toArray();
        }


        switch ($Mode) {
            case static::MERGE:
                $meta = array_merge($this->metadata, $metadata);
                break;

            case static::REPLACE:
                $this->clearMetadata();
                $meta = $metadata;
                break;

            default:
                // Merge by default.
                $meta = array_merge($this->metadata, $metadata);
                break;
        }



        // Add the metadata to the post.
        foreach ($meta as $key => $value) {
            $this->addMetadatum($key, $value);
        }
    }



    /**
     * Add a single metadata item to the post.
     * @param string $key The key for the metadata item.
     * @param mixed $value The value for the metadata item.
     * @return void
     */
    public function addMetadatum(string $key, mixed $value): void
    {
        // Force the key to be lowercase.
        $key = strtolower($key);

        $this->metadata[$key] = $value;
    }



    /**
     * Get the value of a specific metadata item.
     * @param string $key The key for the metadata item.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value of the metadata item or the default value if it does not exist.
     */
    public function getMetadatum(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }



    /**
     * Clear the metadata and return the PostMetadata object.
     * @return PostMetadata The PostMetadata object with cleared metadata.
     */
    public function clear(): static
    {
        $this->clearMetadata();

        return $this;
    }



    /**
     * Set the metadata and return the PostMetadata object.
     * @param array $metadata The metadata to set for the post.
     * @param string $Mode The mode to set the metadata. Can be 'merge' or 'replace'.
     * @return PostMetadata The PostMetadata object with the set metadata.
     */
    public function set(array $metadata, string $Mode = 'replace'): static
    {
        $this->setMetadata($metadata, $Mode);

        return $this;
    }



    /**
     * Add a single metadata item and return the PostMetadata object.
     * @param string $key The key for the metadata item.
     * @param mixed $value The value for the metadata item.
     * @return PostMetadata The PostMetadata object with the added metadata item.
     */
    public function add(string $key, mixed $value): static
    {
        $this->addMetadatum($key, $value);

        return $this;
    }



    /**
     * Get the value of a specific metadata item or return a default value if it does not exist.
     * @param string $key The key for the metadata item.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The value of the metadata item or the default value if it does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getMetadatum($key, $default);
    }



    /**
     * Get all public metadata attributes.
     * @return array An array of metadata attributes that do not start with 'secret'.
     */
    public function getPublicMetadata(): array
    {
        // Return the metadata attributes whose key does not start with 'secret'.
        return array_filter($this->metadata, function ($key) {
            return !str_starts_with('secret', $key);
        }, ARRAY_FILTER_USE_KEY);
    }



    /**
     * Get the special 'id' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getId(?string $default = null): ?string
    {
        return $this->getMetadatum('id', $default);
    }



    /**
     * Get the special 'title' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getTitle(?string $default = null): ?string
    {
        return $this->getMetadatum('title', $default);
    }



    /**
     * Get the special 'description' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getDescription(?string $default = null): ?string
    {
        return $this->getMetadatum('description', $default);
    }



    /**
     * Get the special 'author' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getAuthor(?string $default = null): ?string
    {
        return $this->getMetadatum('author', $default);
    }



    /**
     * Get the special 'image' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getImage(?string $default = null): ?string
    {
        return $this->getMetadatum('image', $default);
    }



    /**
     * Get the special 'tags' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getTags(?array $default = []): ?array
    {
        return $this->getMetadatum('tags', $default);
    }



    /**
     * Get the special 'publicationdate' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getPublicationDate(?string $default = null): ?string
    {
        return $this->getMetadatum('date_published', $default);
    }



    /**
     * Get the special 'updatedate' metadata attribute.
     * @param mixed $default The default value to return if the key does not exist.
     */
    public function getUpdateDate(?string $default = null): ?string
    {
        return $this->getMetadatum('date_updated', $default);
    }



    public function getKapirValue(): array
    {
        return $this->getPublicMetadata();
    }



    /**
     * Parse a YAML string into a PostMetadata object.
     * @param string $yaml The YAML string to parse.
     * @return PostMetadata The PostMetadata object with the parsed data.
     */
    public static function parseFromYaml(string $yaml): static
    {
        // Parse the YAML string into an associative array.
        $data = Yaml::parse($yaml, Yaml::PARSE_OBJECT);



        // Create a new PostMetadata object and set the parsed data.
        $postMetadata = new static();

        $postMetadata->setMetadata($data);



        // Return the PostMetadata object.
        return $postMetadata;
    }



    /**
     * Convert the metadata to an array.
     * @return array The metadata as an associative array.
     */
    public function toArray(): array
    {
        // Convert the metadata to an array.
        return $this->metadata;
    }
}