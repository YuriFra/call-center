function countUp(maxNum, id, timeInterval) {
    let display = 0
    let interval = setInterval(function counting() {
        display += 1;
        document.getElementById(id).innerHTML = display

        if (display === maxNum
        ) {
            clearInterval(interval);
        }
    }, timeInterval);
}
countUp(212, 'number1', 10);


function countUp3(maxNum, id, timeInterval) {
    let display = 0
    let interval = setInterval(function counting() {
        display += 1;
        document.getElementById(id).innerHTML = display

        if (display === maxNum
        ) {
            clearInterval(interval);
        }
    }, timeInterval);
}
countUp3(189, 'number2', 12);

function countUp2(maxNum, id, timeInterval) {
    let display = 0
    let interval = setInterval(function counting() {
        display += 1;
        document.getElementById(id).innerHTML = display

        if (display === maxNum
        ) {
            clearInterval(interval);
        }
    }, timeInterval);
}
countUp2(185, 'number3', 20);
