const staticText = document.getElementById("statictext");
const textForm = document.getElementById("textform");
const textBox = document.getElementById("textbox");

const adminText = document.querySelector("div.admin__text");
const userText = document.querySelector("div.user__text");
const formAssist = document.querySelector("div.form__assist");

let startTime, endTime, counter;
let funcStatus = true;

// Timer ---------------
const secElm = document.querySelector("span.sec");
const minElm = document.querySelector("span.min");
let sec = 0;
let min = 0;

function startTimer() {
  counter = setInterval(() => {
    sec++;
    if (sec < 10) {
      secElm.innerHTML = `0${sec}`;
    } else if (sec >= 10) {
      secElm.innerHTML = sec;
    }
    if (sec == 60) {
      min++;
      sec = 0;
    }
    if (min < 10) {
      minElm.innerHTML = `0${min}`;
    } else if (min >= 10) {
      minElm.innerHTML = min;
    }
  }, 1000);
}
let fixedTime, fixedTimerID;
function fixedTimer() {
  let mins = Math.floor(fixedTime / 60);
  let secs = fixedTime % 60;

  mins = mins < 10 ? "0" + mins : mins;
  secs = secs < 10 ? "0" + secs : secs;
  minElm.innerHTML = mins;
  secElm.innerHTML = secs;
  fixedTime--;
}

const uwpm = document.querySelector("div.user__wpm");
const crwrd = document.querySelector("div.crct__word");
const incrwrd = document.querySelector("div.incrct__word");
const towrds = document.querySelector("div.totalword");
const ttwrd = document.querySelector("div.total__word");
const accu = document.querySelector("div.accurecy");
const ttime = document.querySelector("div.total__time");

const resModal = new bootstrap.Modal(document.getElementById("staticBackdrop"));

textForm.addEventListener("submit", (e) => {
  e.preventDefault();
  clearInterval(counter);
  secElm.innerHTML = "00";
  minElm.innerHTML = "00";
  if (textBox.value.trim().length > 50) {
    formAssist.classList.remove("show");
    resModal.show();
  } else {
    formAssist.classList.add("show");
    resModal.hide();
  }
  adminText.innerHTML = staticText.innerText;
  const userTotalText = textBox.value.split(" ");
  let wrongwords = [];
  userTotalText.forEach((word) => {
    if (!staticText.innerText.split(" ").includes(word)) {
      wrongwords.push(word);
    }
  });
  let txvl = textBox.value;
  wrongwords.forEach((wrqords) => {
    txvl = txvl.replace(wrqords, `<span>${wrqords}</span>`);
  });

  endTime = new Date().getTime();
  const totalTime = (endTime - startTime) / 1000;

  const userAccu =
    100 - ((100 * wrongwords.length) / userTotalText.length).toFixed();
  userText.innerHTML = txvl;
  uwpm.innerHTML = ((userTotalText.length / totalTime) * 60).toFixed();
  crwrd.innerHTML = userTotalText.length - wrongwords.length;
  incrwrd.innerHTML = wrongwords.length;
  towrds.innerHTML = staticText.innerText.split(" ").length;
  ttwrd.innerHTML = userTotalText.length;
  accu.innerHTML = `${userAccu}%`;
  ttime.innerHTML = `${min} minutes, ${sec} seconds`;

  textBox.value = "";
});

textBox.addEventListener("input", () => {
  console.log("hii");
  if (textBox.value.trim().length > 50) {
    formAssist.classList.remove("show");
  }
});

// Settings -------------------------

const timesettings = document.querySelectorAll(
  ".setting__time .form-check-input"
);

timesettings.forEach((time) =>
  time.addEventListener("click", () => {
    timesettings.forEach((tme) => (tme.checked = false));
    time.checked = true;
  })
);

const disbackSpace = document.getElementById("flexCheckDisback");
let disback;
disbackSpace.addEventListener("click", () => {
  if (disbackSpace.checked) {
    disback = true;
  } else {
    disback = false;
  }
});

textBox.addEventListener("focus", () => {
  timesettings.forEach((time) => {
    if (time.checked && time.value > 0) {
      console.log(time.value, time.checked && time.value > 0);
      fixedTime = time.value * 60;
      fixedTimerID = setInterval(fixedTimer, 1000);
    } else if (time.checked && time.value === "0") {
      startTime = new Date().getTime();
      startTimer();
    }
  });
  // startTime = new Date().getTime();
  // startTimer();
  textBox.addEventListener("keydown", (e) => {
    const keycode = e.keyCode || e.charCode;
    if (disback && keycode === 8) {
      e.preventDefault();
    } else {
      e.returnValue = true;
    }
  });
});
textBox.addEventListener("blur", () => {
  clearInterval(counter);
  clearInterval(fixedTimerID);
});
