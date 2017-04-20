Librairie ORM Mélanie2
======================

Développé par le PNE Annuaire et Messagerie/MEDDE

ATTENTION
---------

La version de l'ORM sur git est une version en développement, **elle ne doit pas être utilisée en production**.
Pour récupérer les versions de production veuillez vous adresser au PNE Annuaire et Messagerie du MEDDE.
Cette version peut être utilisée pour le développement d'applications en utilisant une base de données spécifique.


Définition ORM
--------------

Un ORM (object-relationnal mapping) va permettre de faire le lien entre une base de
données et des objets. Ces objets seront formattés de façon à être facilement exploitable par les
applications.
Notre librairie est un ORM écrit en PHP qui permet le mapping de la base de données Horde
Mélanie2.


Intérêt de cette librairie pour Mélanie2
----------------------------------------

La base de données Mélanie2 (Horde) a un schema très spécifique qui n'a pas évolué depuis
des années. Or, de plus en plus d'applications de présentation et de synchronisation utilisent cette
base de données. L'idée est donc de faciliter le développement de ces applications en proposant des
méthodes de développement simple pour l'accès à ces données. De plus des API de type service
Web peuvent être proposé afin d'implémenter cette librairie pour d'autres langages que le PHP.


INSTALLATION
------------

L'installation de l'ORM est expliquée dans le fichier INSTALL.md disponible dans le même projet.


LICENCE
-------

L'ORM Mélanie2 est distribuée sous licence GPLv3 (http://www.gnu.org/licenses/gpl.html)

ORM M2 Copyright © 2017  PNE Annuaire et Messagerie/MEDDE

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

