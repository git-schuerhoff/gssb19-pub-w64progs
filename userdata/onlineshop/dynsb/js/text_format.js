function setFormat(format)
{
  if(format=="email")
  {
    var aTag1 = '<a href="mailto:'; 
    var aTag2 = '">';
    var eTag = '</a>';
  }
  else if(format=="link")
  {
    var aTag1 = '<a href="';
    var aTag2 = '">';
    var eTag = '</a>';
  }
  else if(format=="hr")
  {
    var aTag = "<hr>";
  }
  else
  {
    var aTag = "<"+format+">";
    var eTag = "</"+format+">";
  }
  var input = document.getElementById('conthtml');
  input.focus();

  /* für Internet Explorer */
  if(typeof document.selection != 'undefined')
  {
    /* Einfügen des Formatierungscodes */
    var range = document.selection.createRange();
    var insText = range.text;
    if(format=="email" || format=="link")
    {
      range.text = aTag1 + insText + aTag2 + insText + eTag;
    }
    else if(format=="hr")
    {
      range.text = insText + aTag;
    }
    else
    {
      range.text = aTag + insText + eTag;
    }
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if (insText.length == 0)
    {
      if(format=="hr")
      {
        range.move('character', aTag.length);
      }
      else
      {
        range.move('character', -eTag.length);
      }
    }
    else
    {
      if(format=="hr")
      {
        range.moveStart('character', aTag.length + insText.length);
      }
      else if(format=="email" || format=="link")
      {
        range.moveStart('character', aTag1.length + insText.length + aTag2.length + insText.length + eTag.length);
      }
      else
      {
        range.moveStart('character', aTag.length + insText.length + eTag.length);
      }
    }
    range.select();
  }
  /* für andere Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einfügen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    if(format=="email" || format=="link")
    {
      input.value = input.value.substr(0, start) + aTag1 + insText + aTag2 + insText + eTag + input.value.substr(end);
    }
    else if(format=="hr")
    {
      input.value = input.value.substr(0, start) + insText + aTag + input.value.substr(end);
    }
    else
    {
      input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
    }
    /* Anpassen der Cursorposition */
    var pos;
    if (insText.length == 0)
    {
      pos = start + aTag.length;
    }
    else
    {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
}
