let salary = $("#preference_monthlySalary");
let hours = $("#preference_weeklyHours");
let charges = $("#preference_monthlyCharges");
let holidays = $("#preference_publicHolidaysWeeks");

salary.on('change',updateEveryThings);
hours.on('change',updateEveryThings);
charges.on('change',updateEveryThings);
holidays.on('change',updateEveryThings);


function updateEveryThings(){
    getCoutHoraire();
    getChargeByHour();
}

function getWorkedWeeks() {
    let resp = 52 - parseFloat(holidays.val());
    $("b#semaines-travaillees ").html(resp.toFixed(0));
    return resp;
}

function getMonthlyHours() {
    let resp = (parseFloat(hours.val()) * getWorkedWeeks()) / 12;
    $("b#heures-par-mois").html(resp.toFixed(3));
    return resp;
}

function getCoutHoraire() {
    let resp = parseFloat(salary.val()) / getMonthlyHours();
    $("b#cout-horaire ").html(resp.toFixed(3));
    return resp;
}

function getChargeByHour(){
    let resp = parseFloat(charges.val()) / getMonthlyHours();
    $("b#charges-par-heure ").html(resp.toFixed(3));
    return resp;
}
salary.trigger('change');
