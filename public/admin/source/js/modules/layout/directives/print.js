define(['./../module'], function (module) {
    function printDirective() {
      var printSection = document.getElementById("printSection");
      
      function printElement(elem) {
        // clones the element you want to print
        var domClone = elem.cloneNode(true);
        if (!printSection) {
          printSection = document.createElement("div");
          printSection.id = "printSection";
          document.body.appendChild(printSection);
        } else {
          printSection.innerHTML = "";
          printSection.style.display = 'block';
        }
        printSection.appendChild(domClone);
      }

      function link(scope, element, attrs) {
        element.on("click", function () {
          var elemToPrint = document.getElementById(attrs.printElementId);
          if (elemToPrint) {
            printElement(elemToPrint);
            window.print();
            printSection.style.display = 'none';
          }
        });
      }

      return {
        link: link,
        restrict: "A"
      };
    }

    module.directive("ngPrint", [printDirective]);
});