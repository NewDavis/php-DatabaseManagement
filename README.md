<h1>php-DatabaseManagement</h1>

<h2>Documentation:</h2>

<h3>To-Do</h3>
<ul>
    <li><b>Table-Creation</b>: Command / Migration-System to create and update all tables</li>
    <li>
        <b>WriteBuilder</b>: Fix write order, to write multiple entities with relations at once. <br>
        The write process is quite slow (because of password hashing).
    </li>
    <li><b>Write</b>: ManyToMany is only set for first or last created entity when creating as batch?</li>
    <li><b>ConditionBuilder</b>: Add missing functionality for sorting</li>
</ul>
 
<h3>Connection:</h3>
<ul>
    <li>You have to go into your project <b>.env</b> File</li>
    <li>
        Check if there is a <b>DATABASE_URL</b> configured:<br>
        For example: DATABASE_URL="mysql://root:@localhost:3307/development?"
    </li>
    <li>The connection should now work.</li>
</ul>

<h3>Create your own Entity (example: User):</h3>
<ul>
    <li>Create a new folder with the name of your entity in the <b>src/Entity</b> directory.</li>
    <li>Now in that folder you will have to create three classes: <b>UserEntity</b>, <b>UserCollection</b>, <b>UserDefinition</b></li>
    <li>
        <h4>UserDefinition:</h4>
        <ul>
            <li>
                First your Entity class needs to extend from <b>NewDavis\DatabaseManagement\Core\Entity\EntityDefinition</b>
            </li>
            <li>
                Now your Entity class needs to implements <b>NewDavis\DatabaseManagement\Core\Entity\EntityDefinitionInterface</b>
            </li>
            <li>
                On top of your class create a const with the name <b>ENTITY_NAME</b>:<br>
                for example: <br>
<pre><code>public const ENTITY_NAME = 'user';</code></pre>
            </li>
            <li>
                For the <b>getEntityName</b> function, return your const.<br>
                for example: <br>
                <pre><code>public function getEntityName(): string|null
{
    return self::ENTITY_NAME;
}</code></pre>
            </li>
            <li>
                For the <b>getEntityClass</b> function return your UserEntity class.<br>
                for example: <br>
                <pre><code>public function getEntityClass(): string|null
{
    return UserEntity::class;
}</code></pre>
            </li>
            <li>
                For the <b>getCollectionClass</b> function return your UserCollection class.<br>
                for example: <br>
                <pre><code>public function getCollectionClass(): string|null
{
    return UserCollection::class;
}</code></pre>
            </li>
            <li>
                For the <b>getPropertyDefinition</b> function return an array with all your Properties.<br>
                <b>Note: You don't need to integrate the IdProperty, CreatedAtProperty and UpdatedAtProperty, these will automatically be added.</b><br>
                for example: <br>
                <pre><code>public function getPropertyDefinition(): array
{
    return [
        new AutoIncrementProperty(),
        new Property('name', 'VARCHAR', 255, [new Unique(), new Required()]),
    ];
}</code></pre>
                Here we have defined a AutoIncrementProperty, that is not required, but it will create a property in your database for your entity.<br>
                Also we defined a regular Property. We gave it the propertyName <b>name</b>, we have set the type of the property to <b>VARCHAR</b>, so the database knows its an text. The 255 defines the allowed maximum length that can be stored. And at the end we create an array of flags for example here we have a Unique flag so the name is unique and also we set the Required flag that means that it needs this property to be saved.
            </li>
        </ul>
    </li>
    <li>
        <h4>UserCollection:</h4>
        <ul>
            <li>
                First your UserCollection class needs to extend from <b>NewDavis\DatabaseManagement\Core\Entity\EntityCollection</b>
            </li>
            <li>
                Now you have to add this comment and put it above your class
                <pre><code>/**
 * @extends EntityCollection
 *
 * @method UserEntity first()
 * @method UserCollection[]|null search(Criteria $criteria)
 * @method UserCollection[]|null searchBy(string $property, string $value)
 */
class UserCollection extends EntityCollection</code></pre>
            </li>
        </ul>
    </li>
    <li>
        <h4>UserEntity:</h4>
        <ul>
            <li>
                First your UserEntity class needs to extend from <b>NewDavis\DatabaseManagement\Core\Entity\Entity</b>
            </li>
            <li>
                In this class you have to create for all your properties a variable.<br>
                for example:
<pre><code>class UserEntity extends Entity
{
&nbsp;
use IdTrait;
use AutoIncrementTrait;
use CreatedAtTrait;
use UpdatedAtTrait;
&nbsp;
protected string $name;
&nbsp;
public function getName(): string
{
    return $this->name;
}
&nbsp;
public function setName(string $name): static
{
    $this->name = $name;
    &nbsp;
    return $this;
}</code></pre>
            For default fields, there are Traits, so you just have to use them in your Entity class and that will automatically add the variable and the getters and setters for that.
            </li>
        </ul>
    </li>
</ul>