/**
 * Ce fichier est développé pour la gestion des API de la librairie Mélanie2
 * Ces API permettent d'accéder à la librairie en REST
 *
 * ORM API Copyright © 2022  Groupe MCD/MTE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

var commands = [];

/**
 * Envoi de la requête
 */
function send() {
    document.body.style.cursor = 'wait'
    document.querySelector('#result').value = "Lancement de la requête...";

    const apikey = document.querySelector('#apikey').value,
        url = document.querySelector('#url').value
        method = document.querySelector('#method').value;

    addToHistory({ method: method, url: url });

    let myHeaders = new Headers();
    myHeaders.append("Authorization", "Apikey " + apikey);

    let myInit = { 
        method: method,
        headers: myHeaders,
        cache: 'default'
    };

    if (method == 'POST') {
        myHeaders.append("Content-type", "application/json");
        myInit.body = document.querySelector('#json').value;
    }

    let myRequest = new Request(url, myInit);

    fetch(myRequest, myInit).then(function(response) {
        let contentType = response.headers.get("content-type");
        document.body.style.cursor = 'default'
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json().then(function(json) {
                // traitement du JSON
                document.querySelector('#result').value = JSON.stringify(json, null, 2);
            });
        } else {
            document.querySelector('#result').value = "Oops, nous n'avons pas du JSON!";
        }
    }); 
}

/**
 * Rafraichi la liste des commandes
 */
function refreshHistory() {
    document.querySelector('#commands').innerHTML = '';
    for (const key in window.commands) {
        if (Object.hasOwnProperty.call(window.commands, key)) {
            const element = window.commands[key];
            let option = document.createElement('option');
            option.value = element.method + '|' + element.url;
            option.text = element.method + " " + element.url;
            document.querySelector('#commands').add(option, null);
        }
    }
}

/**
 * Ajoute la commande a l'historique
 * 
 * @param {object} command 
 */
function addToHistory(command) {
    for (const key in window.commands) {
        if (Object.hasOwnProperty.call(window.commands, key)) {
            const element = window.commands[key];
            if (element.method == command.method && element.url == command.url) {
                delete window.commands[key];
            }
        }
    }
    window.commands.unshift(command);
    refreshHistory();
}

/**
 * Changement dans le select
 * 
 * @param {string} value 
 */
function change(value) {
    if (value == 'POST') {
        document.querySelector('#request div.data').style.display = 'block';
    }
    else {
        document.querySelector('#request div.data').style.display = 'none';
    }
}

/**
 * Permet de rejouer une commande
 * @param {string} value 
 */
function command(value) {
    let command = value.split('|');
    document.querySelector('#method').value = command[0];
    document.querySelector('#method').onchange();
    document.querySelector('#url').value = command[1];
}

/**
 * Initialisation du script
 */
function init() {
    change(document.querySelector('#method').value);
    document.querySelector('#result').value = "";
}

init();