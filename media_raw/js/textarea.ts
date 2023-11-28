const replacements = {
	[String.fromCharCode(8220)]: '"', // curly quote
	[String.fromCharCode(8221)]: '"', // curly quote
	[String.fromCharCode(8216)]: "'", // curly quote
	[String.fromCharCode(8217)]: "'", // curly quote
	[String.fromCharCode(8211)]: "-", // en dash
	[String.fromCharCode(8212)]: "--", // em dash
	[String.fromCharCode(189)]: "1/2", // 1/2
	[String.fromCharCode(188)]: "1/4", //1/4
	[String.fromCharCode(190)]: "3/4", //3/4
	[String.fromCharCode(169)]: "(C)", // (C)
	[String.fromCharCode(174)]: "(R)", // (R)
	[String.fromCharCode(9)]: " ", // tab
	[String.fromCharCode(8230)]: "...", // ellipsis
	[String.fromCharCode(160)]: " ", // non-breaking space
	[String.fromCharCode(8232)]: " ", // line separator
	[String.fromCharCode(8233)]: " ", // paragraph separator
	[String.fromCharCode(8234)]: " ", // left-to-right embedding
	[String.fromCharCode(8235)]: " ", // right-to-left embedding
	[String.fromCharCode(8236)]: " ", // pop directional formatting
	[String.fromCharCode(8237)]: " ", // left-to-right override
	[String.fromCharCode(8238)]: " ", // right-to-left override
	[String.fromCharCode(8239)]: " ", // narrow no-break space
	[String.fromCharCode(8288)]: " ", // word joiner
	[String.fromCharCode(65279)]: " ", // zero-width no-break space
	[String.fromCharCode(8203)]: " ", // zero-width space
	[String.fromCharCode(8204)]: " ", // zero-width non-joiner
	[String.fromCharCode(8205)]: " ", // zero-width joiner
	[String.fromCharCode(8206)]: " ", // left-to-right mark
	[String.fromCharCode(8207)]: " ", // right-to-left mark
	[String.fromCharCode(8226)]: "*", // bullet
};

function CLAWmaxarea(textarea: HTMLTextAreaElement, counterspan: HTMLDivElement, max_chars: number) {
	if ( max_chars < 1) {
		return;
	}

	let current_value = textarea.value;
	var current_length = current_value.length;
	var remaining_chars = max_chars - current_length;
	let w = 0;

	const bar = counterspan.getElementsByTagName('div')[0];

	if ( remaining_chars <= max_chars * 0.1 ) {
		bar.style.backgroundColor = "red";
		
		if ( remaining_chars+1 <= 0 ) {
			// Crop the text
			current_value=current_value.substring(0, max_chars-1);
			textarea.value = current_value;

			remaining_chars = 0;
		}
	} else {
		bar.style.backgroundColor = "green";
	}

	wordScrubber(textarea);
	// current_value = Word_Entity_Scrubber.scrub(current_value);
	
	w = current_value.length / max_chars * 100;
	
	if ( w >= 100 ) {
		w=100;
	}
	
	bar.setAttribute('aria-valuenow', w.toString());
	bar.style.width = w.toString() + '%';

	const bartext = counterspan.getElementsByTagName('span')[0];
	bartext.innerHTML = `${current_value.length} / ${max_chars}`;
};

function wordScrubber(textarea: HTMLTextAreaElement) {
	let current_value = textarea.value;

	for (const [key, value] of Object.entries(replacements)) {
    const regex = new RegExp(key, "gi");
    current_value = current_value.replace(regex, value);
	}
	
	textarea.value = current_value;
}

// Setup the event handlers
document.addEventListener("DOMContentLoaded", function () {
	var textareas = document.getElementsByClassName('counted');

	if (textareas) {
		for (let i = 0; i < textareas.length; i++) {
			// Is it textarea
			if (textareas[i].tagName != 'TEXTAREA') {
				continue;
			}
			// Is there a counter
			let counter = parseInt(textareas[i].getAttribute('maxlength'));
			if ( counter < 1) {
				continue;
			}

			// Insert Bootstrap progress bar below the textarea
			let progress = document.createElement('div');
			progress.setAttribute('class', 'progress');
			var bar = document.createElement('div');
			bar.setAttribute('class', 'progress-bar');
			bar.setAttribute('role', 'progressbar');
			bar.setAttribute('aria-valuenow', '0');
			bar.setAttribute('aria-valuemin', '0');
			bar.setAttribute('aria-valuemax', '100');
			progress.appendChild(bar);

			var progressSpan = document.createElement('span');
			progressSpan.setAttribute('class', 'progress-text');
			progress.appendChild(progressSpan);

			textareas[i].parentNode.insertBefore(progress, textareas[i].nextSibling);

			textareas[i].addEventListener('focus', () => CLAWmaxarea(textareas[i] as HTMLTextAreaElement, progress, counter), false);
			textareas[i].addEventListener('input', () => CLAWmaxarea(textareas[i] as HTMLTextAreaElement, progress, counter), false);
		}
	}
	
});
