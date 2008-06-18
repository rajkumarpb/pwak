<refentry id="{@id}">
    <refnamediv>
        <refname>Generic grid</refname>
        <refpurpose></refpurpose>
    </refnamediv>

{@toc}

    <refsynopsisdiv>
        <refsynopsisdivinfo>
            <author>bruno, bruno@ateor.com - </author>
            <author>izi, izi@ateor.com - </author>
            <author>guil, guil@ateor.com</author>
            <copyright>Copyright @ 2007, ATEOR</copyright>
        </refsynopsisdivinfo>
    </refsynopsisdiv>
    <refsect1 id="{@id description}">
        <title>Description</title>
        <para>This class build a generic grid for an object using getMapping() 
        and getFeatures() methods of this object.</para>
        <para>See the codegen manual to know how build compliant objects.</para>
    </refsect1>
    <refsect1 id="{@id usage}">
        <title>Usage</title>
        <refsect2 id="{@id basicUsage}">
            <title>Basic usage</title>
            <para>Make a php file named dispatcher.php and put Dispatcher::dispatch().
                <programlisting role="php">
                    <![CDATA[
/**
 * dispatcher.php
 */

try {
    Dispatcher::dispatch();
} catch(Exception $exc) {
    Tools::redirectTo('home.php');
    exit();
}
                    ]]>
                </programlisting>
                Now to make a grid of your object Foo, juste call
                <![CDATA[dispatcher.php?entity=Foo]]>
            </para>

        </refsect2>
        <refsect2 id="{@id advancedUsage}">
            <title>Advanced usage</title>
            <para>For an advanced usage, you can create a class FooGrid in
            PROJECTROOT/lib/Custom/FooGrid.php
                <programlisting role="php">
                    <![CDATA[
/**
 * Class FooGrid.
 *
 * Custom class to use GenericGrid with object Foo.
 *
 */
class FooGrid extends GenericGrid {
    /**
     * Constructor.
     *
     * @param string $entity Object class name
     * @param array $params Grid params
     * @return void
     */
    public function __construct($entity, $params=array()) {
        pararams['itemperpage'] = 15; 
        parent::__construct($entity, $params);
    }

    /**
     * Custom render for the bar property in the SearchForm.
     *
     * @return void
     */
    public function renderSearchFormBar() {
    }
}
                    ]]>
                </programlisting>
            When dispatcher will be call with entity=Foo, this class will be
            used to make GenericGrid. To make many grids to the same object, you
            can make Foo1Grid, Foo2Grid classes (in Foo1Grid.php, Foo2Grid.php)
            then call <![CDATA[dispatcher.php?entity=Foo&altname=Foo1]]>
            </para>
        </refsect2>
    </refsect1>
</refentry>
