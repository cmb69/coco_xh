# Coco_XH

Coco_XH ermöglicht eine beliebige Anzahl so genannter Co-Contents
auf Ihrer Website zu nutzen.
Diese sind vergleichbar mit Newsboxen,
aber haben unterschiedlichen Inhalt auf jeder Seite.
Coco_XH wurde inspiriert vom
[Also Plugin](http://cmsimplewiki-com.keil-portal.de/doku.php?id=plugins:also),
erlaubt aber das Bearbeiten mit dem gewählten Editor anstelle einer
Textarea und der Verwendung von Skripting.

- [Voraussetzungen](#voraussetzungen)
- [Download](#download)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
  - [Suchen](#suchen)
  - [Administration](#administration)
- [Problembehebung](#problembehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Coco_XH ist ein Plugin für [CMSimple_XH](https://www.cmsimple-xh.org/de/).
Es benötigt CMSimple_XH ≥ 1.7.0 und PHP ≥ 7.0.0.

## Download

Das [aktuelle Release](https://github.com/cmb69/coco_xh/releases/latest)
kann von Github herunter geladen werden.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple_XH-Plugins auch.

1. Sichern Sie die Daten auf Ihrem Server.
1. Entpacken Sie die ZIP-Datei auf Ihrem Rechner.
1. Laden Sie das ganze Verzeichnis `coco/` auf Ihren Server
   in das `plugins/` Verzeichnis von CMSimple_XH hoch.
1. Machen Sie die Unterverzeichnisse `css/`
   und `languages/` beschreibbar.
1. Navigieren Sie zu `Plugins` → `Coco` im Administrationsbereich,
   und prüfen Sie, ob alle Voraussetzungen erfüllt sind.

## Einstellungen

Die Plugin-Konfiguration erfolgt wie bei vielen anderen
CMSimple_XH-Plugins auch im Administrationsbereich der Website.
Gehen Sie zu `Plugins` → `Coco`.

Die Lokalisierung wird unter `Sprache` vorgenommen.
Sie können die Zeichenketten in Ihre eigene Sprache übersetzen,
falls keine entsprechende Sprachdatei zur Verfügung steht,
oder sie entsprechend Ihren Anforderungen anpassen.

Das Aussehen von Coco_XH kann unter `Stylesheet` angepasst werden.
Das ist hauptsächlich für das Gestalten des Administrationsbereichs gedacht. 
Das Gestalten der Co-Contents sollte durch Änderungen am Template
und dessen Stylesheet durchgeführt werden.

## Verwendung

Um Co-Content auf Ihrer Hompage zu nutzen,
fügen Sie einfach an der gewünschten Stelle in Ihr Template

    <?php echo coco('mein_content');?>

ein, z.B. anstelle einer Newsbox.
Statt `mein_content` können Sie jeden Namen wählen,
der nur Kleinbuchstaben (`a`-`z`), Ziffern und Unterstriche enthält.
Sie können so viele Co-Contents verwenden wie Sie möchten,
solange Sie diesen unterschiedliche Namen geben.

Der folgende Screenshot zeigt zwei Co-Contents:

![Ansichtmodus](https://github.com/cmb69/coco_xh/raw/master/help/view-mode.png)

Wenn Sie sich im Bearbeitungsmodus befinden,
können Sie den Co-Content mit dem eingestellten Editor bearbeiten.
Möchten Sie eine andere Toolbar verwenden als diejenige,
die für den Haupt-Content-Editor konfiguriert wurde,
geben Sie einfach den Namen der Toolbar als zweiten Parameter an, z.B.

    <?php echo coco('kleiner_content', 'sidebar');?>

Als Toolbar sind üblicherweise "full", "medium", "minimal" und "sidebar" verfügbar.
Wie die Toolbars des TinyMCE angepasst werden können, wird im
[CMSimple_XH-Wiki](https://www.cmsimple-xh.org/wiki/doku.php/de:tinymce#customization)
erklärt.

Der folgende Screenshot zeigt die Co-Contents im Bearbeitungsmodus:

![Bearbeitungsmodus](https://github.com/cmb69/coco_xh/raw/master/help/edit-mode.png)

Die Breite und Höhe der Textarea bzw. des Editors
sind auf 100% der Größes ihres Containers voreingestellt.
Die Höhe kann durch einen dritten Parameter für
den `coco()` Aufruf geändert werden, z.B.

    <?php echo coco('mein_content', 'sidebar', '500px');?>

Wenn Sie diesen Parameter übergeben,
dann müssen Sie auch den Toolbar-Parameter übergeben.
Wenn Sie bei der Standard-Toolbar bleiben möchten,
schreiben Sie einfach:

    <?php echo coco('mein_content', false, '500px');>

Möchten Sie die Breite ändern, können Sie das im Stylesheet tun.

Die Co-Contents werden im Unterordner `coco/` des `content/` Ordners
von CMSimple_XH in einer HTML-Datei mit dem Namen,
der als erster Parameter angegeben wurde, gespeichert,
z.B. `mein_content.htm`.
Die Struktur ist ähnlich zu derjenigen der CMSimple_XH `content.htm`-Datei von Version 1.7 und höher,
aber aus historischen Gründen werden die Seiten entsprechend Ihrer Menüebene durch
`<h1>`, `<h2>` … `<h9>` getrennt, statt durch die entsprechenden HTML-Kommentare.
Die Namen der Seitenüberschriften werden nur zur besseren Lesbarkeit eingefügt;
sie werden von Coco_XH ignoriert.
Statt dessen wird die Verknüpfung zur Seite durch die id der Überschrift hergestellt.
Sie dürfen diese ids auf keinen Fall ändern!
Das Erstellen neuer Seiten durch Einfügen neuer Überschriften ist nicht möglich;
diese werden einfach ignoriert.

Beachten Sie, dass beim Logout eine Sicherungskopie
aller Co-Contents automatisch erstellt wird.
Dies wird auf die gleiche Art gemacht,
wie CMSimple_XH Sicherungskopien des Inhalt anlegt.
Möchten Sie eine Sicherungskopie wiederherstellen,
müssen Sie dies per FTP tun.

### Suchen

Möchten Sie, dass CMSimple_XHs Suchfunktion auch die Co-Contents durchsucht,
müssen Sie `cmsimple/search.php` durch `plugins/coco/search.php` ersetzen. Die Suche funktioniert so wie die von CMSimple_XH 1.5.x.

### Administration

Sie können die Co-Contents unter `Plugins` → `Coco` → `Co-Contents` verwalten.
Zur Zeit ist es dort nur möglich,
nicht mehr benötigte Co-Content Dateien zu löschen.
Dies ist allerdings wichtig,
da alle Co-Content-Dateien automatisch von der Suchfunktion durchsucht werden;
abgesehen davon, dass das Zeit kostet,
könnte es zu falschen Suchergebnissen führen.

## Problembehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf
[Github](https://github.com/cmb69/coco_xh/issues)
oder im [CMSimple\_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Coco_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Coco_XH erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Coco_XH erhalten haben. Falls nicht, siehe <https://www.gnu.org/licenses/>.

Copyright © 2012-2023 Christoph M. Becker

Slovakische Übersetzung © 2012 Dr. Martin Sereday  
Tschechische Übersetzung © 2012 Josef Němec  

## Danksagung

Coco_XH wurde vom
[Also Plugin](http://cmsimplewiki-com.keil-portal.de/doku.php?id=plugins:also)
von Ricardo Serpell inspiriert.

Das Plugin-Icon wurde von [Andy Gongea](https://gongea.com/) gestaltet. 
Vielen Dank für die Veröffentlichung als Freeware.

Vielen Dank an die Community im
[CMSimple_XH-Forum](https://www.cmsimpleforum.com/)
für Tipps, Vorschläge und das Testen.
Besonders möchte ich *snafu* und *Ulrich* danken,
die auf die Nützlichkeit von Also hingewiesen haben,
und die mich bezüglich des Speicherorts für solche Daten umgestimmt haben,
sowie an *Gert* und *snafu*,
die die ersten Bugs gemeldet und den neuen Namen vorgeschlagen haben.

Und zu guter Letzt vielen Dank an
[Peter Harteg](https://www.harteg.dk/), den „Vater“ von CMSimple,
und alle Entwickler von [CMSimple_XH](https://www.cmsimple-xh.org/de/),
ohne die dieses phantastische CMS nicht existieren würde.
