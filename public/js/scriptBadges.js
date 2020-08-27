const OPEN='open';
const CLOSED='closed';
const INPROGRESS='in progress';
const CUSTOMERFEEDBACK='Waiting for customer feedback';

const LOW='low';
const MEDIUM='medium';
const HIGH='high';

let badges =Array.from(document.getElementsByClassName('pilletje'));
badges.forEach((badge) =>{
   let badgeContent=badge.innerHTML;
   console.log(badgeContent.length);
    if(OPEN==badgeContent || LOW==badgeContent){
        badge.style.backgroundColor='green';
    }

    if(MEDIUM==badgeContent || CUSTOMERFEEDBACK==badgeContent || INPROGRESS==badgeContent){
        badge.style.backgroundColor='orange';
    }
    if(HIGH==badgeContent || CLOSED==badgeContent){
        badge.style.backgroundColor='red';
    }



})

console.log(badges)