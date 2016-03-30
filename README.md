# Cethyworks\SwaggerToolsBundle

Development tools for [kleijnweb/swagger-bundle](https://github.com/kleijnweb/swagger-bundle)

Doctrine entities, controllers & access-control file generators based on Swagger documents.

## Install 

```sh
$ composer require cethyworks-swagger-tools-bundle
```

*Needs kleijnweb/swagger-bundle to be configured.*

## Commands

### Doctrine Entities Generator

```sh
$ app/console swagger:generate:entities swagger.yml HelloApiBundle [--exclude=]
```

This command auto-generate doctrine entities based on Swagger file resource definitions.

The resulting entities contains `@ORM\` & `@Assert\` annotations (@see Supported Swagger Properties below).

See `swagger:generate:entities --help` for more details.

#### Id

The id attribute is forced to a guid format & UUID strategy.


#### Relations

The generator does not support relations.


### Controllers Generator

```sh
$ app/console swagger:generate:controllers swagger.yml HelloApiBundle
```

This command auto-generate Controller classes (and corresponding ControllerTest classes) based on the Swagger file paths definitions and register them as services (as needed by `kleijnweb/swagger-bundle`).

The resultant classes contains all the methods declared and matches the routing generated by `kleijnweb/swagger-bundle`.

**How it works (internal behavior) :**

- lists sf2 routes (needs SwaggerBundle working)
- extracts `^swagger.{documentName}.{resource}.*.{action}$` from routes
- build `{resource}Controller.php` with `{action(s)}` methods
- register `swagger.controller.{resource}` service


See `swagger:generate:controllers --help` for more details.


### Role Based Access Control Configuration Generator

```sh
$ app/console swagger:generate:access-control swagger.yml HelloApiBundle
```

This command auto-generate access-control configuration files based on the Swagger file paths/security definitions and register them in `config/security.yml`.

See `swagger:generate:access-control --help` for more details.


### Supported Swagger Properties
- readOnly
- required* (Assert)
- minLength/maxLength (Assert)
- minimum/maximum (Assert)
- minItems/maxItems (Assert)
- enum (Assert)
- type (Assert)
- pattern (Assert)
- format{date\*,date-time\*,email,uuid,url, custom\*}* (Assert)
- x-parent*


#### `required`
Required is defined as an array & at the root level, eg :

```yml
definitions:
  User:
    type: object
    required: ['firstName', 'lastName']
    properties:
      lastName:
        type: string
      firstName:
        type: string
      email:
      	type: string
```

#### `format`
Swagger allow any string as format's value, Use it to add your custom Constraints.

example : `format: "AppBundle\Validator\Constraints\MyCustomConstraint"`


#### `x-parent`
Custom swagger property providing the means to declare a parent class to the entity generated.


### Todo
- Handle one2one relations
- Handle many2many relations
- Handle some basic behavior (timestampable)
- Add `Request $request` on put/post&delete (or detect if paramType body)
- exclude.yml file
- -f/--force instead of -f=true
- handle [Swagger inheritance](https://github.com/OAI/OpenAPI-Specification/blob/master/fixtures/v2.0/json/models/modelWithComposition.json) with allOf


### License
MIT License

### Inspiration
[KleijnWeb\SwaggerBundle](https://github.com/kleijnweb/swagger-bundle) & [KleijnWeb\SwaggerBundleTools](https://github.com/kleijnweb/swagger-bundle-tools)