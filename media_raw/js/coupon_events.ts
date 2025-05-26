function copyCoupons(): void {
	const coupons = document.querySelectorAll("[data-coupon]");
	let text: string = '';

	[...coupons].forEach((e) => {
		text += (e as HTMLElement).dataset.coupon + "\n";
	});

	navigator.clipboard.writeText(text)
		.then(() => {
			alert('Coupons copied to clipboard');
		})
		.catch(err => {
			alert('Error in copying text: '+ err);
		});
}

document.addEventListener('DOMContentLoaded', function() {
	// Callback function to execute when mutations are observed
	const mutationCallback = function(mutationsList: Array<MutationRecord>, observer: MutationObserver) {
		for (let mutation of mutationsList) {
			if (mutation.type === 'childList') {
				// Process newly added nodes with HTMX
				mutation.addedNodes.forEach((node: { nodeType: number; }) => {
					if (node.nodeType === Node.ELEMENT_NODE) {
						htmx.process(node);
					}
				});
			}
		}
	};

	const observer = new MutationObserver(mutationCallback);
	const config = {
		childList: true,
		subtree: true
	};

	// Start observing the form node for mutations
	observer.observe(document.getElementById('claw-coupon-generator'), config);

	document.addEventListener('htmx:configRequest', function(event) {
    // @ts-ignore
		const triggeredElement = event.detail.triggeringEvent.target;
    // @ts-ignore
		event.detail.parameters.htmxChangedField = triggeredElement.name;
	});
});
