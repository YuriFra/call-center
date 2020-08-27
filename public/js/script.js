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
countUp(212, 'number1', 70);


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
countUp3(189, 'number2', 50);

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
countUp2(185, 'number3', 100);

let badges =Array.from(document.getElementsByClassName('pilletje'));
badges.forEach(badge, () =>{
    console.log(badge.innerHTML);
})