<!DOCTYPE html>
<html lang="sk">

<head>
    <title>Alica Ondreakova Page</title>

    <meta name_t="viewport" content="width=device-width, initial-scale=1.0">
    <meta name_t="keywords" content="html, css">
    <meta name_t="author" content="Alica Ondreakova">

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
</head>

<body>
    <select id="country">
        <option value="SK">SK</option>
        <option value="CZ">CZ</option>
        <option value="AT">AT</option>
        <option value="PL">PL</option>
        <option value="HU">HU</option>
    </select>

    <form id="getNamesForDay">
        <fieldset>
            <legend>Najst meniny v den</legend>
            <p>Den: <input type="number" min="1" max="31" name="day"></p>
            <p>Mesiac: <input type="number" min="1" max="12" name="month"></p>
            <input type="submit">
            <p class="result"></p>
            <p class="error"></p>
        </fieldset>
    </form>

    <form id="getDayForName">
        <fieldset>
            <legend>Najst meniny pre meno</legend>
            <p>Meno: <input type="text" name="name"></p>
            <input type="submit">
            <p class="result"></p>
            <p class="error"></p>
        </fieldset>
    </form>

    <form id="getHolidaysForCountry">
        <fieldset>
            <legend>Najst vsetky sviatky pre krajinu</legend>
            <input type="submit">
            <p class="result"></p>
            <p class="error"></p>
        </fieldset>
    </form>

    <form id="getSpecialDaysForCountry">
        <fieldset>
            <legend>Najst pamatne dni pre krajinu</legend>
            <input type="submit">
            <p class="result"></p>
            <p class="error"></p>
        </fieldset>
    </form>

    <form id="addNameDay">
        <fieldset>
            <legend>Pridat meniny do databazy</legend>
            <p>Meno: <input type="text" name="name"></p>
            <p>Den: <input type="number" min="1" max="31" name="day"></p>
            <p>Mesiac: <input type="number" min="1" max="12" name="month"></p>
            <input type="submit">
            <p class="result"></p>
            <p class="error"></p>
        </fieldset>
    </form>

    <h1>
        Popis API
</h1>
<p>API je jsonrpc api. Dotazy sa posielaju ako POST na api.php. Mozne metody:</p>
<p>Mena pre datum:</p>
<code>{"jsonrpc": "2.0", "method": "getNamesForDay", "params": {"day": "0101", "country": "SK"}, "id": 0}</code>
<p>Datum pre meno:</p>
<code>{"jsonrpc": "2.0", "method": "getDayForName", "params": {"name": "Alica", "country": "SK"}, "id": 0}</code>
<p>Sviatky pre krajinu:</p>
<code>{"jsonrpc": "2.0", "method": "getHolidaysForCountry", "params": {"country": "SK"}, "id": 0}</code>
<p>Pamatne dni pre krajinu:</p>
<code>{"jsonrpc": "2.0", "method": "getSpecialDaysForCountry", "params": {"country": "SK"}, "id": 0}</code>
<p>Pridat meniny:</p>
<code>{"jsonrpc": "2.0", "method": "addNameDay", "params": {"name": "Alica", "day": "0906", "country": "SK"}, "id": 0}</code>


    <script type="text/javascript">
        var currentCountry = "SK";
        var countrySel = document.getElementById("country");
        countrySel.onchange = function () {
            currentCountry = countrySel.value;
        };
        var reqId = 0;

        function callMethod(method, params, onSuccess, onError) {
            params.country = currentCountry;
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: JSON.stringify({
                    'jsonrpc': '2.0',
                    'method': method,
                    'params': params,
                    'id': reqId++,
                }),
                dataType: 'json',
                contentType: 'application/json',
                success: function(data) {
                    if (data.error !== undefined) {
                        var err = 'Api error with code: ' + data.error.code + ' Message: ' + data.error.message;
                        if (data.error.data !== undefined) {
                            err += ' Data: ' + data.error.data;
                        }
                        onError(err);
                    } else {
                        onSuccess(data.result);
                    }
                },
                error: function() {
                    onError('Error occured calling the api.')
                },
            });
        }

        function pad(num, size) {
            var s = num + "";
            while (s.length < size) s = "0" + s;
            return s;
        }

        function niceDay(mmdd) {
            return mmdd.substr(2, 2) + '. ' + mmdd.substr(0, 2) + '.';
        }

        var getNamesForDayForm = document.getElementById("getNamesForDay");
        getNamesForDayForm.onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(getNamesForDayForm);
            callMethod(
                "getNamesForDay", {
                    'day': pad(formData.get('month'), 2) + pad(formData.get('day'), 2)
                },
                function(names) {
                    var result = $(getNamesForDayForm).find('.result');
                    result.empty().show();
                    if (names.length === 0) {
                        result.append('Ziadne mena pre dany den a krajinu');
                    }
                    for (var i = 0; i < names.length; ++i) {
                        result.append($('<p></p>').text(names[i]));
                    }
                    $(getNamesForDayForm).find('.error').hide();
                },
                function(err) {
                    $(getNamesForDayForm).find('.error').show().text(err);
                    $(getNamesForDayForm).find('.result').hide();
                });
        }

        var getDayForNameForm = document.getElementById("getDayForName");
        getDayForNameForm.onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(getDayForNameForm);
            callMethod(
                "getDayForName", {
                    'name': formData.get('name'),
                },
                function(day) {
                    $(getDayForNameForm).find('.result').show().text(niceDay(day));
                    $(getDayForNameForm).find('.error').hide();
                },
                function(err) {
                    $(getDayForNameForm).find('.error').show().text(err);
                    $(getDayForNameForm).find('.result').hide();
                });
        }

        var getSpecialDaysForCountryForm = document.getElementById("getSpecialDaysForCountry");
        getSpecialDaysForCountryForm.onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(getSpecialDaysForCountryForm);
            callMethod(
                "getSpecialDaysForCountry", {},
                function(holidays) {
                    var result = $(getSpecialDaysForCountryForm).find('.result');
                    result.empty().show();
                    if (holidays.length === 0) {
                        result.append('Ziadne pamatne dni pre danu krajinu');
                    }
                    for (var i = 0; i < holidays.length; ++i) {
                        result.append($('<p></p>').text(niceDay(holidays[i].day) + ' ' + holidays[i].name));
                    }
                    $(getSpecialDaysForCountryForm).find('.error').hide();
                },
                function(err) {
                    $(getSpecialDaysForCountryForm).find('.error').show().text(err);
                    $(getSpecialDaysForCountryForm).find('.result').hide();
                });
        }

        var getHolidaysForCountryForm = document.getElementById("getHolidaysForCountry");
        getHolidaysForCountryForm.onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(getHolidaysForCountryForm);
            callMethod(
                "getHolidaysForCountry", {},
                function(holidays) {
                    var result = $(getHolidaysForCountryForm).find('.result');
                    result.empty().show();
                    if (holidays.length === 0) {
                        result.append('Ziadne sviatky pre danu krajinu');
                    }
                    for (var i = 0; i < holidays.length; ++i) {
                        result.append($('<p></p>').text(niceDay(holidays[i].day) + ' ' + holidays[i].name));
                    }
                    $(getHolidaysForCountryForm).find('.error').hide();
                },
                function(err) {
                    $(getHolidaysForCountryForm).find('.error').show().text(err);
                    $(getHolidaysForCountryForm).find('.result').hide();
                });
        }

        var addNameDayForm = document.getElementById("addNameDay");
        addNameDayForm.onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(addNameDayForm);
            callMethod(
                "addNameDay", {
                    'day': pad(formData.get('month'), 2) + pad(formData.get('day'), 2),
                    'name': formData.get('name'),
                },
                function(names) {
                    $(addNameDayForm).find('.result').show().text('Meno pridane');
                    $(addNameDayForm).find('.error').hide();
                },
                function(err) {
                    $(addNameDayForm).find('.error').show().text(err);
                    $(addNameDayForm).find('.result').hide();
                });
        }

    </script>
</body>

</html>