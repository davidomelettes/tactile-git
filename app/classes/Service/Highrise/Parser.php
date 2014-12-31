<?php
class Service_Highrise_Parser {
  private $reader   = "";
  private $section  = "";
  private $callback = "";
  
  function __construct($data, $section, $callback="", $type=0)
   {
    $this->reader   = new XMLReader();
    $this->section  = $section;
    $this->callback = $callback;

    if($type === 0)
     {
      // File
      $this->reader->open($data); 
     }
    else
     {
      // String
      $this->reader->XML($data);
     }
   }

  // http://us2.php.net/manual/en/ref.domxml.php#71493
  // Need to see if we can optimize this function.
  function xml2array($domnode)
   {
    $nodearray = array();
    $domnode   = $domnode->firstChild;

     while(!is_null($domnode))
      {
       $currentnode = $domnode->nodeName;

       switch ($domnode->nodeType)
        {
         case XML_TEXT_NODE:
          if(!(trim($domnode->nodeValue) == "")) $nodearray['cdata'] = $domnode->nodeValue;
         break;

         case XML_ELEMENT_NODE:
          if($domnode->hasAttributes())
           {
            $elementarray = array();
            $attributes   = $domnode->attributes;

            foreach($attributes as $index => $domobj)
             {
              $elementarray[$domobj->name] = $domobj->value;
             }
           }
         break;
        }

       if($domnode->hasChildNodes())
        {
         $nodearray[$currentnode][] = $this->xml2array($domnode);

         if(isset($elementarray))
          {
           $currnodeindex = count($nodearray[$currentnode]) - 1;
           $nodearray[$currentnode][$currnodeindex]['@'] = $elementarray;
          }
        }
       else
        {
         if(isset($elementarray) && $domnode->nodeType != XML_TEXT_NODE)
          {
           $nodearray[$currentnode]['@'] = $elementarray;
          }
        }
       $domnode = $domnode->nextSibling;
      }
    return $nodearray;
   }

  function parseIt()
   {
    while($this->reader->read())
     {
      if($this->reader->nodeType == 1 &&
         $this->reader->localName == $this->section)
       {
        do
         { 
          // Expand the rest of the data
          $element = $this->reader->expand(); 

          // Call back
          $call = $this->callback;
          $call($this->xml2array($element));
         }while($this->reader->next($this->section)); 
        break; 
       } 
     }
   } 
 }

?>