[![Build Status](https://travis-ci.com/alamirault/elasticsearch-form-bundle.svg?token=asaWxyRVYo9KqM3Qcx8Q&branch=master)](https://travis-ci.com/alamirault/elasticsearch-form-bundle)


Overview
=============
This Symfony bundle allows to create Form and transform fields to an elasticsearch query. 
Once you created your form type you will be able to update an elasticsearch query from a form type.

The idea is:
1. Create a form type, foreach field pass option `elastic_filter_condition`
2. Use `ElasticsearchMaker` to submit search, paginate, sort.

Installation
------------
1. Run `composer require alamirault/elasticsearch-form-bundle`

2. Register the bundle in your `app/AppKernel.php`:

   ``` php
    <?php
    ...
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Alamirault\ElasticsearchBundle\AlamiraultElasticsearchFormBundle(),
            ...
        );
    ...
   ```
   
## Configuration

```
alamirault_elasticsearch_form:
    clients:
        default:
            connections:
                - { host: "elasticsearch_host", port: 9200, proxy: null }
            logger: logger.api
            indexes:
                users:
                    mapping: '%kernel.project_dir%/src/Alamirault/LogsBundle/Resources/elasticsearch/mapping/change.yml'
```
  
Usage
-----

In ExampleFilterType class:
``` php
     public function buildForm(FormBuilderInterface $builder, array $options)
        {
                    $builder->add('uuid', TextType::class, [
                        'elastic_filter_condition' => function (?string $value) {
                            if (is_null($value)) {
                                return;
                            }
        
                            return new Match('uuid', $value);
                        },
                    ])
        }
```

In controller to make a search page list: 
``` php
    public function indexAction(Request $request, FormFactoryInterface $formFactory, EntityManagerInterface $entityManager,
                                TranslatorInterface $translator, BoLogEntryDenormalizer $denormalizer)
    {
        $form = $formFactory->create(ExampleFilterType::class);

        $sortableChanges = $this->elasticsearchMaker->manageForm($form, $request, $this->indexRegistry,
            "changes", $denormalizer);

        return $this->render('AlamiraultLogsBundle:Changes:index.html.twig',
            [
                "form" => $form->createView(),
                "sortableChanges" => $sortableChanges,
            ]
        );
    }
```

Search users by uuid begins by value:
```php
        $matchLike = new Query\Wildcard('uuid', $value."*");
        $query = new Query();
        $query->setQuery($matchLike);
        $query->setSource(["object_uuid"]);

        $resultSet = $this->elasticsearchMaker->makeSearch($this->indexRegistry, "users", $query);
```

Publish document
```php
            $normalized = $serializer->normalize($user) //User is an object;

            $doc = new Document($user->getUuid(), $normalized);

            $index = $this->indexMaker->getIndex($this->indexRegistry, "users", IndexMaker::WRITE);

            $type = new Type($index, '_doc'); // Only if elasticsearch <7.2.0
            $type->addDocuments([$doc]);
```

## Testing 

```php
./vendor/bin/phpunit --bootstrap vendor/autoload.php Tests/
```