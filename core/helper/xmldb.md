#XML


##XML

###Create safe xml data. Removes dangerous characters for string.

    $xml_safe = XML::safe($xml_unsafe);

###Get XML file

    $xml_file = XML::loadFile('path/to/file.xml');


##DB

Create new database


    DB::create('db_name');


##Table

Table construct

    xmldb('table_name');
    OR
    $users = new Table('table_name');


###Create new table

    Table::create('table_name', array('field1', 'field2'));

###Delete table

    Table::drop('table_name');

###Get table

    $table = Table::get('table_name');

###Get information about table

    var_dump($users->info());

###Get table fields

    var_dump($users->fields());

###Add new field

    $users->addField('test');

###Delete field

    $users->deleteField('test');

###Add new record

    $users->insert(array('login'=>'admin', 'password'=>'pass'));

###Select record(s) in table

    $records = $users->select('[id=2]');

    $records = $users->select(null, 'all');

    $records = $users->select(null, 'all', null, array('login'));

    $records = $users->select(null, 2, 1);

###Delete current record in table

    $users->delete(2);

###Delete with xPath query record in xml file

    $users->deleteWhere('[id=2]');

###Update record with xPath query in XML file

    $users->updateWhere('[id=2]', array('login'=>'Admin', 'password'=>'new pass'));

###Update current record in table

    $users->update(1, array('login'=>'Admin','password'=>'new pass'));

###Get last record id

    echo $users->lastId();

###Get count of records

    echo $users->count();
