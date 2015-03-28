if(typeof Newnorth === "undefined") {
	Newnorth = {};
}

/* window
/* * * * */

window.GetAnchorVariableValue = function(pvAnchorVariableName, pvDefaultValue) {
	var match = window.location.hash.match("(?:#|&)" + pvAnchorVariableName + "=(.*?)(?:&|$)");

	if(match === null) {
		return pvDefaultValue;
	}
	else {
		return match[1];
	}
};

/* Node
/* * * * */

Node.prototype.GetAttributeValue = function(pvAttributeName, pvDefaultValue) {
	var value = this.getAttribute(pvAttributeName);

	if(value === null) {
		return pvDefaultValue;
	}
	else {
		return value;
	}
};

/* Newnorth.Event
/* * * * */

Newnorth.Event = function() {
	this.ivListeners = [];
};

Newnorth.Event.prototype.AddListener = function(pvObject, pvMethod) {
	this.ivListeners.push({
		ivObject: pvObject,
		ivMethod: pvMethod,
	});
};

Newnorth.Event.prototype.Invoke = function(pvInvoker, pvData) {
	for(var i = 0; i < this.ivListeners.length; ++i) {
		this.ivListeners[i].ivMethod.call(
			this.ivListeners[i].ivObject,
			pvInvoker,
			pvData
		);
	}
};

/* Newnorth.Controls
/* * * * */

Newnorth.Controls = {};

Newnorth.Controls.Find = function(pvParent, pvType) {
	var elements = pvParent.getElementsByTagName("*");

	for(var i = 0; i < elements.length; ++i) {
		var type = elements[i].getAttribute("Newnorth:Control");

		if(type === pvType) {
			if(!elements[i].ivIsControl) {
				Newnorth.Controls[type].call(elements[i]);
			}

			return elements[i];
		}
	}

	return null;
}

Newnorth.Controls.Control = function() {
	this.ivIsControl = true;

	this.FindChildNodeByAttributeName = function(pvAttributeName) {
		var elements = this.getElementsByTagName("*");

		for(var i = 0; i < elements.length; ++i) {
			if(elements[i].getAttribute(pvAttributeName) !== null) {
				return elements[i];
			}
		}

		return null;
	}
};

Newnorth.Controls.ErrorMessagesControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivErrorMessages = [];

	this.HasErrorMessage = function(pvText) {
		for(var i = 0; i < this.ivErrorMessages.length; ++i) {
			if(this.ivErrorMessages[i].ivText === pvText) {
				return true;
			}
		}

		return false;
	};

	this.AddErrorMessage = function(pvText) {
		if(!this.HasErrorMessage(pvText)) {
			var element = document.createElement("p");

			element.innerHTML = pvText;

			this.appendChild(element);

			this.style.display = "block";

			this.ivErrorMessages.push({ivElement: element, ivText: pvText});
		}
	};

	this.RemoveErrorMessage = function(pvText) {
		for(var i = 0; i < this.ivErrorMessages.length; ++i) {
			if(this.ivErrorMessages[i].ivText !== pvText) {
				continue;
			}

			this.removeChild(this.ivErrorMessages[i].ivElement);

			if(this.childNodes.length === 0) {
				this.style.display = "none";
			}

			this.ivErrorMessages.splice(i, 1);

			return;
		}
	};
};

Newnorth.Controls.ValidatableControl = function() {
	this.ivIsValidatable = true;

	this.ivIsValid = true;

	this.ivIsDefaultValue = true;

	this.ivValidators = [];

	this.ivErrorMessages = Newnorth.Controls.Find(this.ivContainer === undefined ? this : this.ivContainer, "ErrorMessagesControl");

	this.AddValidator = function(pvMethod, pvErrorMessage) {
		if(this.ivIsValid && !pvMethod.call(this)) {
			this.ivIsValid = false;

			if(this.ivForm !== undefined) {
				this.ivForm.ivIsValid = false;
			}
		}

		this.ivValidators.push({ivMethod: pvMethod, ivErrorMessage: pvErrorMessage});
	};

	this.Validate = function() {
		this.ivIsValid = true;

		if(this.ivIsDefaultValue || this.ivErrorMessages === null) {
			for(var i = 0; i < this.ivValidators.length; ++i) {
				if(!this.ivValidators[i].ivMethod.call(this)) {
					this.ivIsValid = false;
				}
			}
		}
		else {
			for(var i = 0; i < this.ivValidators.length; ++i) {
				if(this.ivValidators[i].ivMethod.call(this)) {
					this.ivErrorMessages.RemoveErrorMessage(this.ivValidators[i].ivErrorMessage);
				}
				else {
					this.ivErrorMessages.AddErrorMessage(this.ivValidators[i].ivErrorMessage);

					this.ivIsValid = false;
				}
			}
		}
	};
};

Newnorth.Controls.FormControl = function() {
	this.ivIsValid = true;

	this.ivOnEnterValidStateEvent = new Newnorth.Event();

	this.ivOnEnterInvalidStateEvent = new Newnorth.Event();

	this.ivControls = [];

	this.ivOnAnyValueChangedEvent = new Newnorth.Event();

	{
		var anyValueChangedEvent = this.getAttribute("Newnorth:AnyValueChangedEvent");

		if(anyValueChangedEvent !== null) {
			this.ivOnAnyValueChangedEvent.AddListener(this, new Function(["pvControl", "pvValue"], anyValueChangedEvent));
		}
	}

	this.AddControl = function(pvControl) {
		if(pvControl.ivIsValidatable === true) {
			this.ivIsValid = this.ivIsValid && pvControl.ivIsValid;
		}

		this.ivControls.push(pvControl);
	};

	this.Validate = function() {
		var isValid = true;

		for(var i = 0; i < this.ivControls.length; ++i) {
			if(this.ivControls[i].Validate !== undefined) {
				this.ivControls[i].Validate();

				isValid = isValid && this.ivControls[i].ivIsValid;
			}
		}

		if(this.ivIsValid !== isValid) {
			this.ivIsValid = isValid;

			if(this.ivIsValid) {
				this.ivOnEnterValidStateEvent.Invoke(this, null);
			}
			else {
				this.ivOnEnterInvalidStateEvent.Invoke(this, null);
			}
		}
	};

	this.GetValuesAsString = function() {
		var values = "";

		for(var i = 0; i < this.ivControls.length; ++i) {
			if(0 < this.ivControls[i].name.length) {
				values += "&" + this.ivControls[i].name + "=" + encodeURIComponent(this.ivControls[i].GetValue());
			}
		}

		return values.substring(1);
	};

	this.onsubmit = function() {
		for(var i = 0; i < this.ivControls.length; ++i) {
			this.ivControls[i].ivIsDefaultValue = false;
		}

		this.Validate();

		return this.ivIsValid;
	};
};

Newnorth.Controls.HiddenControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivForm = this.form;

	this.ivAnchorVariable = this.GetAttributeValue("Newnorth:AnchorVariable", "");

	this.ivValue = this.value;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnValueChanged = new Newnorth.Event();

	this.IsValueChangedMethod = function() {
		if(this.ivValue !== this.value) {
			this.ivValue = this.value;

			this.ivOnValueChanged.Invoke(this, this.value);

			if(this.ivForm !== null) {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);
			}
		}
	};

	if(0 < this.ivAnchorVariable.length) {
		var value = window.GetAnchorVariableValue(this.ivAnchorVariable, this.GetValue());

		this.SetValue(value);

		this.ivValue = this.value;

		window.addEventListener(
			"hashchange",
			{
				ivControl: this,
				handleEvent: function() {
					var value = window.GetAnchorVariableValue(this.ivControl.ivAnchorVariable, this.ivControl.GetValue());

					this.ivControl.SetValue(value);

					this.ivControl.IsValueChangedMethod();
				}
			}
		);
	}

	this.ivForm.AddControl(this);
};

Newnorth.Controls.TextBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivAnchorVariable = this.ivContainer.GetAttributeValue("Newnorth:AnchorVariable", "");

	this.ivValue = this.value;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnValueChanged = new Newnorth.Event();

	this.IsValueChangedMethod = function() {
		if(this.ivValue !== this.value) {
			this.ivValue = this.value;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("keydown", this.IsValueChangedMethod);

	this.addEventListener("keyup", this.IsValueChangedMethod);

	if(0 < this.ivAnchorVariable.length) {
		var value = window.GetAnchorVariableValue(this.ivAnchorVariable, this.GetValue());

		this.SetValue(value);

		this.ivValue = this.value;

		window.addEventListener(
			"hashchange",
			{
				ivControl: this,
				handleEvent: function() {
					var value = window.GetAnchorVariableValue(this.ivControl.ivAnchorVariable, this.ivControl.GetValue());

					this.ivControl.SetValue(value);

					this.ivControl.IsValueChangedMethod();
				}
			}
		);
	}

	this.ivForm.AddControl(this);
};

Newnorth.Controls.TextAreaBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivValue = this.value;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnValueChanged = new Newnorth.Event();

	var isValueChangedMethod = function() {
		if(this.ivValue !== this.value) {
			this.ivValue = this.value;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("keydown", isValueChangedMethod);

	this.addEventListener("keyup", isValueChangedMethod);

	this.ivForm.AddControl(this);
};

Newnorth.Controls.PasswordBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivValue = this.value;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnValueChanged = new Newnorth.Event();

	var isValueChangedMethod = function() {
		if(this.ivValue !== this.value) {
			this.ivValue = this.value;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("keydown", isValueChangedMethod);

	this.addEventListener("keyup", isValueChangedMethod);

	this.ivForm.AddControl(this);
};

Newnorth.Controls.DropDownListControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivAnchorVariable = this.ivContainer.GetAttributeValue("Newnorth:AnchorVariable", "");

	this.ivValue = this.selectedIndex;

	this.GetValue = function() {
		return this.options[this.selectedIndex].value;
	};

	this.SetValue = function(pvValue) {
		for(var i = 0; i < this.options.length; ++i) {
			if(this.options[i].value === pvValue) {
				this.selectedIndex = i;

				return;
			}
		}

		throw new Error("Invalid value.");
	};

	this.ivOnValueChanged = new Newnorth.Event();

	this.IsValueChangedMethod = function() {
		if(this.ivValue !== this.selectedIndex) {
			this.ivValue = this.selectedIndex;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.options[this.selectedIndex].value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.options[this.selectedIndex].value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("change", this.IsValueChangedMethod);

	this.addEventListener("keydown", this.IsValueChangedMethod);

	this.addEventListener("keyup", this.IsValueChangedMethod);

	if(0 < this.ivAnchorVariable.length) {
		var value = window.GetAnchorVariableValue(this.ivAnchorVariable, this.GetValue());

		this.SetValue(value);

		this.ivValue = this.selectedIndex;

		window.addEventListener(
			"hashchange",
			{
				ivControl: this,
				handleEvent: function() {
					var value = window.GetAnchorVariableValue(this.ivControl.ivAnchorVariable, this.ivControl.GetValue());

					this.ivControl.SetValue(value);

					this.ivControl.IsValueChangedMethod();
				}
			}
		);
	}

	this.ivForm.AddControl(this);
};

Newnorth.Controls.CheckBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivChecked = this.checked;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnIsCheckedChanged = new Newnorth.Event();

	var isIsCheckedChangedMethod = function() {
		if(this.ivChecked !== this.checked) {
			this.ivChecked = this.checked;

			this.ivIsDefaultValue = false;

			this.ivOnIsCheckedChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("click", isIsCheckedChangedMethod);

	this.addEventListener("keydown", isIsCheckedChangedMethod);

	this.addEventListener("keyup", isIsCheckedChangedMethod);

	this.ivForm.AddControl(this);
};

Newnorth.Controls.CollapsableSectionControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivIsExpanded = true;

	this.ivToggleElement = this.FindChildNodeByAttributeName("Newnorth:ToggleElement");

	this.ivToggleElement.ivCollapsableSectionControl = this;

	this.ivToggleElement.addEventListener("click", function(){this.ivCollapsableSectionControl.Toggle()});

	this.ivContentsElement = this.FindChildNodeByAttributeName("Newnorth:ContentsElement");

	this.Expand = function() {
		this.className = this.className.replace(/(^| )CollapsedSection( |$)/, " ").trim() + " ExpandedSection";

		this.ivContentsElement.style.height = this.ivContentsElement.scrollHeight + "px";
	};

	this.Collapse = function() {
		this.className = this.className.replace(/(^| )ExpandedSection( |$)/, " ").trim() + " CollapsedSection";

		this.ivContentsElement.style.height = "0px";
	};

	this.Toggle = function() {
		if(this.ivIsExpanded) {
			this.ivIsExpanded = false;

			this.Collapse();
		}
		else {
			this.ivIsExpanded = true;

			this.Expand();
		}
	};

	this.Refresh = function() {
		if(this.ivIsExpanded) {
			var height = 0;

			for(var i = 0; i < this.ivContentsElement.childNodes.length; ++i) {
				var child = this.ivContentsElement.childNodes[i];

				if(child.offsetTop !== undefined && child.offsetHeight !== undefined) {
					height = Math.max(height, child.offsetTop + child.offsetHeight);
				}
			}

			this.ivContentsElement.style.height = height + "px";
		}
	};

	if(this.ivIsExpanded) {
		this.Expand();
	}
	else {
		this.Collapse();
	}

	var observer = new MutationObserver(function(){this.ivCollapsableSectionControl.Refresh()});

	observer.ivCollapsableSectionControl = this;

	observer.observe(this.ivContentsElement, {childList: true, subtree: true});
};

window.addEventListener(
	"load",
	function() {
		var elements = document.getElementsByTagName("*");

		for(var i = 0; i < elements.length; ++i) {
			var type = elements[i].getAttribute("Newnorth:Control");

			if(type === null) {
				continue;
			}

			if(Newnorth.Controls[type] === undefined) {
				throw new Error("There's no control of the type \"" + type + "\".");
			}

			Newnorth.Controls[type].call(elements[i]);
		}
	}
);