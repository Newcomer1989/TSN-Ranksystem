// Damit diese Funktion funktioniert, legen Sie bitte eine TXT-Datei mit dem Namen faviconurl.txt im Mainverzeichnis vom Ranksystem an. In diese Datei geben Sie die Verlinkung auf Ihren favicon an, was auf der Webseite angezeigt werden soll.
// For this function to work, please create a TXT file with the name faviconurl.txt in the main directory of the ranking system. In this file, enter the link to your favicon, which is to be displayed on the website.
document.addEventListener('DOMContentLoaded', function() {
    fetch('../faviconurl.txt')
        .then(response => {
        if (response.ok) {
            // Datei ist vorhanden
            return response.text();
        }
    })


    .then(text => {
        if (text != undefined) {
            // Hier kann der Inhalt der Textdatei verarbeitet werden
            var link = document.querySelector("[rel='icon']");
            link.setAttribute("href", text);
        }
    });
});