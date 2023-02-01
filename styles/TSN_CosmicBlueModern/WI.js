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

document.addEventListener('DOMContentLoaded', function(event) {

    var collapse = document.getElementsByClassName('collapse');

    var countCollapse = collapse.length;

    if(countCollapse>0){

        var x = '';

        var allCollapse = [];
        
        for(x=0;x<countCollapse;x++){
            if(collapse[x].id!=''){
                allCollapse.push(collapse[x].id);
            }
        }

        allCollapse.forEach(function(value) {

            var targetCollaps = document.getElementById(value);

            targetCollaps.className = 'collapse';

            targetCollaps.Style = 'height: 0;';

            targetCollaps.setAttribute('aria-expanded', 'false');

            var enabledButten = document.querySelector("[data-target='#"+value+"']");

            enabledButten.setAttribute('aria-expanded', 'false');
            
            var children = document.getElementById(value).getElementsByTagName("li");
            var hasClass = false;
            for (var i = 0; i < children.length; i++) {
              if (children[i].classList.contains("active")) {
                hasClass = true;
                break;
              }
            }

            if (hasClass) {
                var classValue = "activates";
                document.querySelector('[data-target="#' + value + '"]').className = classValue;
            } else {
                document.querySelector('[data-target="#' + value + '"]').className = "";
            }

        })
    }
    
});

document.addEventListener("mouseup", function(event) {

    var collapse = document.getElementsByClassName('collapse');

    var countCollapse = collapse.length;

    if(countCollapse>0){

        var x = '';

        var allCollapse = [];
        
        for(x=0;x<countCollapse;x++){
            if(collapse[x].id!=''){
                allCollapse.push(collapse[x].id);
            }
        }

        var clickedElement = event.target;

        var dataTarget = clickedElement.getAttribute("data-target");

        if (dataTarget===null||dataTarget===undefined||dataTarget=='') {

            clickedElement = clickedElement.parentElement;
            dataTarget = clickedElement.getAttribute("data-target");

            if (dataTarget===null||dataTarget===undefined||dataTarget=='') {

                clickedElement = clickedElement.parentElement;
                dataTarget = clickedElement.getAttribute("data-target");

            }
        }

        /*var cleckedElementClass = clickedElement.parentElement.className;

        clickedElement.parentElement.className = 'active' + cleckedElementClass;*/

        allCollapse.forEach(function(value) {

            if('#' + value != dataTarget){

                var targetCollaps = document.getElementById(value);

                targetCollaps.className = 'collapse';

                targetCollaps.Style = 'height: 0;';

                targetCollaps.setAttribute('aria-expanded', 'false');

                var enabledButten = document.querySelector("[data-target='#"+value+"']");

                enabledButten.setAttribute('aria-expanded', 'false');
                
            }

        })
    }
    
});