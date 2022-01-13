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

var uwpm = document.querySelector("div.user__wpm");
var crwrd = document.querySelector("div.crct__word");
var incrwrd = document.querySelector("div.incrct__word");
var towrds = document.querySelector("div.totalword");
var ttwrd = document.querySelector("div.total__word");
var accu = document.querySelector("div.accurecy");
var ttime = document.querySelector("div.total__time");
var msswrd = document.querySelector("div.missing__word");
var extrawrd = document.querySelector("div.extra__word");
var totincrwrd = document.querySelector("div.totincrct__word");
var dphElem = document.querySelector("div.dph");
var keyPressed = 0;

var resModal = new bootstrap.Modal(document.getElementById("staticBackdrop"));

textForm.addEventListener("submit", (e) => {
	e.preventDefault();
	clearInterval(counter);
	secElm.innerHTML = "00";
	minElm.innerHTML = "00";

	if (textBox.value.trim().length > 20) {
		formAssist.classList.remove("show");
		resModal.show();
	} else {
		formAssist.classList.add("show");
		resModal.hide();
	}
	$.ajax({
		type: "POST",
		url: "check.php",
		data: {
			userText: textBox.value.trim(),
			staticText: staticText.innerHTML.trim(),
		},
		success: callback,
	});

	/* Old code
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
  */
	// end old code
	function callback(results) {
		const {
			wrongWords,
			missingWords,
			extraWords,
			goodWords,
			inputText,
			startingText,
		} = results;
		console.log(results);

		endTime = new Date().getTime();
		const totalTime = (endTime - startTime) / 1000;

		//text replacement
		msswrd.innerHTML = missingWords;
		extrawrd.innerHTML = extraWords;

		userText.innerHTML = inputText.join(" ");
		adminText.innerHTML = startingText.join(" ");
		crwrd.innerHTML = goodWords;
		incrwrd.innerHTML = wrongWords;
		towrds.innerHTML = startingText.length;
		ttwrd.innerHTML = textBox.value.split(" ").length;
		totalError = Math.abs(missingWords) + wrongWords;
		totincrwrd.innerHTML = totalError;

		//metrics

		const userAccu =
			100 - ((100 * totalError) / inputText.length).toFixed();
		console.log(userAccu);
		uwpm.innerHTML =
			((inputText.length / totalTime) * 60).toFixed() + " WPM";
		accu.innerHTML = `${userAccu}%`;
		ttime.innerHTML = `${min} minutes, ${sec} seconds`;
		const dph = keyPressed / (totalTime / 3600);
		dphElem.innerText = dph.toFixed();

		textBox.value = "";
		keyPressed = 0;
	}
});

textBox.addEventListener("input", () => {
	if (textBox.value.trim().length > 20) {
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

// It will count only type-addicted key
function handleKeyDown(e) {
	const keycode = e.keyCode;
	console.log(keycode);
	if (
		(keycode > 47 && keycode < 91) ||
		(keycode > 96 && keycode < 112) ||
		(keycode > 159 && keycode < 166) ||
		keycode == 170 ||
		keycode == 171 ||
		keycode == 173 ||
		(keycode > 186 && keycode < 232) ||
		keycode == 32
	) {
		console.log(keyPressed);
		keyPressed++;
	} else {
		if (keycode !== 8) {
			e.preventDefault();
		}
	}
}

textBox.addEventListener("focus", () => {
	timesettings.forEach((time) => {
		if (time.checked && time.value > 0) {
			//console.log(time.value, time.checked && time.value > 0);
			fixedTime = time.value * 60;
			fixedTimerID = setInterval(fixedTimer, 1000);
		} else if (time.checked && time.value === "0") {
			startTime = new Date().getTime();
			startTimer();
		}
	});
	startTime = new Date().getTime();

	startTimer();

	textBox.addEventListener("keydown", handleKeyDown);
});

textBox.addEventListener("blur", () => {
	clearInterval(counter);
	clearInterval(fixedTimerID);
	textBox.removeEventListener("keydown", handleKeyDown);
});
