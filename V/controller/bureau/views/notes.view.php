<h1>Notizzettel</h1>
<style>
form[name=notizzettel] textarea {
    background-color: #555555;
    width: 80%;
    margin: 10px;
    margin-left: auto;
    margin-right: auto;
    color: white;
    border: #222222;
    display: block;
    padding: 5px;
}
</style>
<form name="notizzettel">
    <textarea name="notiz" style="width: 80%; height: 200px" onchange="TSC.bureau.set('notiz', this)"><?= escape($notiz) ?></textarea>
</form>


<h2>Dokumente</h2>
<a href="../Einfuehrung_in_das_Webmodul.pdf" target="_blank">Hilfe zur Bedienung des Spielermoduls</a>

<br><br>

<h2>Home sweet home</h2>
<a href="../index.php">Das Modul verlassen</a>
<br>
<br>
<br>