-----------------------------------------------------------------------
--                              tables                               --
-----------------------------------------------------------------------
[[foreach item=entity from=$entities]]

[[if $entity.parentclass eq 'Object']]
--
-- Table structure for [[$entity.tablename]]
--
DROP TABLE IF EXISTS [[$entity.tablename]];
CREATE TABLE [[$entity.tablename]] (
  _Id int(11) unsigned NOT NULL default 0,
  _DBId int(11) default 0,
[[if $entity.isparent]]
  _ClassName VARCHAR(255) DEFAULT NULL,
[[/if]]
[[foreach item=property from=$entity.allproperties]]
[[if in_array($property.type.const, array('TYPE_STRING','TYPE_HEXACOLOR','TYPE_PASSWORD','TYPE_FILE','TYPE_IMAGE','TYPE_FILE_UPLOAD','TYPE_EMAIL','TYPE_URL'))]]
  _[[$property.name]] [[$property.type.sqltype]]([[$property.length]]) DEFAULT [[$property.sqldefault]],
[[elseif $property.type.const eq 'TYPE_TEXT']]
  _[[$property.name]] TEXT DEFAULT [[$property.sqldefault]],
[[elseif in_array($property.type.const, array('TYPE_LONGTEXT','TYPE_HTML'))]]
  _[[$property.name]] LONGTEXT DEFAULT [[$property.sqldefault]],
[[elseif in_array($property.type.const, array('TYPE_INT','TYPE_CONST'))]]
  _[[$property.name]] INT([[$property.length]])[[if $property.required]] NOT NULL[[/if]] DEFAULT [[$property.sqldefault]],
[[elseif $property.type.const == 'TYPE_FLOAT']]
  _[[$property.name]] FLOAT[[if $property.required]] NOT NULL[[/if]] DEFAULT [[$property.sqldefault]],
[[elseif $property.type.const == 'TYPE_DECIMAL']]
  _[[$property.name]] DECIMAL([[$property.length]])[[if $property.required]] NOT NULL[[/if]] DEFAULT [[$property.sqldefault]],
[[elseif $property.type.const == 'TYPE_BOOL']]
  _[[$property.name]] INT(1)[[if $property.required]] NOT NULL[[/if]] DEFAULT [[$property.sqldefault]],
[[elseif in_array($property.type.const, array('TYPE_DATE', 'TYPE_DATETIME', 'TYPE_DATE'))]]
  _[[$property.name]] [[$property.type.sqltype]][[if $property.required]] NOT NULL[[/if]] DEFAULT [[$property.sqldefault]],
[[elseif in_array($property.type.const, array('TYPE_FKEY', 'TYPE_I18N_STRING', 'TYPE_I18N_TEXT', 'TYPE_I18N_HTML'))]]
  _[[$property.name]] INT([[$property.length]]) NOT NULL DEFAULT [[$property.sqldefault]],
[[/if]]
[[/foreach]]
[[if $entity.trackchanges]]
  _LastModified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
[[/if]]
  PRIMARY KEY (_Id)
) TYPE=InnoDB CHARSET=latin1;

[[foreach item=property from=$entity.allproperties]]
[[if $property.unique]]
CREATE UNIQUE INDEX _[[$property.name]] ON [[$entity.tablename]] (_[[$property.name]]);
[[elseif $property.type.const == 'TYPE_FKEY']]
CREATE INDEX _[[$property.name]] ON [[$entity.tablename]] (_[[$property.name]]);
[[/if]]
[[/foreach]]
[[/if]]
[[/foreach]]
[[if $links]]
-----------------------------------------------------------------------
--                           Link tables                             --
-----------------------------------------------------------------------
[[foreach item=link from=$links]]

--
-- Table structure for [[$link.tablename]]
--
DROP TABLE IF EXISTS [[$link.tablename]];
CREATE TABLE [[$link.tablename]] (
  _[[$link.field]] int(11) unsigned NOT NULL default 0,
  _[[$link.linkfield]] int(11) unsigned NOT NULL default 0,
  PRIMARY KEY (_[[$link.field]], _[[$link.linkfield]])
) TYPE=InnoDB CHARSET=latin1;
[[/foreach]]
[[/if]]
