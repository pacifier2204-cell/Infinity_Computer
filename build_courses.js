const fs = require('fs');

const courses = [
  // 4 original
  {
      name: "Computer Training Programs", duration: "Flexible", bgColor: "#0d6efd",
      topics: ["Computer basics and office productivity", "Internet and digital communication practices", "Practical assignments and guided mentoring"],
      designedFor: "Students, job seekers, and professionals seeking stronger day-to-day capability."
  },
  {
      name: "SAP Courses", duration: "Focused Modules", bgColor: "#198754",
      topics: ["Module-oriented curriculum support", "Practical case-based training approach", "Career pathway guidance and progression advice"],
      designedFor: "Candidates aiming to build enterprise software competency and understanding."
  },
  {
      name: "Skill Development", duration: "Batch Based", bgColor: "#0dcaf0", textColor: "#000", bgOpacity: "rgba(0,0,0,0.1)",
      topics: ["Hands-on learning model", "Batch planning for different levels", "Individual support for learning progress"],
      designedFor: "Building practical confidence and relevance in modern digital tools."
  },
  {
      name: "Career-Oriented Learning", duration: "Varies by Pathway", bgColor: "#6f42c1",
      topics: ["Interview readiness support", "Industry-aware learning direction", "Performance-focused coaching"],
      designedFor: "Improving employability by combining technical learning with application-driven outcomes."
  },
  
  // New courses
  {
      name: "TALLY", duration: "1 ½ Months", bgColor: "#d63384",
      topics: ["Company Creation", "Ledger Creation", "Group Creation", "Voucher Creation", "Bill Wise Details", "Inventory Control", "Backup and Restore Data", "GST Setup", "IGST, SGST, CGST, CESS", "Email Config & Cheque Printing"],
  },
  {
      name: "D.T.P. (Desktop Publishing)", duration: "1 ½ Months", bgColor: "#fd7e14", textColor: "#000", bgOpacity: "rgba(0,0,0,0.1)",
      topics: ["Introduction of Corel-Draw", "Introduction of Toolbar", "LOGO Creation", "Introduction of Page Maker", "Toolbar, Command Reference", "Menu Introduction", "Introduction of Photo Shop", "Toolbar, Working with Layers", "Photo Effect"],
  },
  {
      name: "BASIC", duration: "1 ½ Months", bgColor: "#20c997",
      topics: ["Computer Fundamentals", "Hardware Concepts", "Operating System", "Microsoft Office", "Microsoft Word", "Microsoft Excel", "Microsoft PowerPoint", "Internet & Basic Online Work’s", "Regional Language Typing (Gujarati, Hindi or any other)"],
  },
  {
      name: "Web Designing", duration: "1 ½ Months", bgColor: "#0dcaf0", textColor: "#000", bgOpacity: "rgba(0,0,0,0.1)",
      topics: ["HTML", "DHTML", "JAVA SCRIPT", "DREAMWEAVER", "Concept of Hosting", "Domain & SEO"],
  },
  {
      name: "C Programming", duration: "1 ½ Months", bgColor: "#0d6efd",
      topics: ["Introduction of Programming", "Classes & Objects", "Condition, Control Statement", "Array, user defines functions", "Sting manipulation", "Working with files"],
  },
  {
      name: "C++ Programming", duration: "1 ½ Months", bgColor: "#6610f2",
      topics: ["Fundamental of Programming", "Class Definition", "Inheritance", "Virtual Function", "Constructors & Destructors", "Managing console I/O", "Type Conversion"],
  },
  {
      name: "Hardware & Networking", duration: "6 Months", bgColor: "#dc3545",
      topics: ["Introduction of Hardware &Networking", "Bios, Motherboard, CPU, RAM, & Hard Disk, Writer, Cd Rom, DVD", "Key Board, Mouse, Monitor, Printer", "Cabinet, UPS Fault Finding", "Troubleshooting, Assembling", "Operating System, Windows XP, 2007", "Drivers Installation, Disk Management", "Disk Cleanup / Backup, LAN, WAN & MAN", "Modem, Switch & Hub", "Connecting Computer", "Networking Operating Systems"],
  },
  {
      name: "Python Level 1", duration: "1 1/2 Months", bgColor: "#ffc107", textColor: "#000", bgOpacity: "rgba(0,0,0,0.1)",
      topics: ["Vital Python – Math, Strings, Conditionals, and Loops", "Variables", "Strings: Concatenation, Methods, and input ()", "Strings and Their Methods", "Python Structures", "Executing Python – Programs", "Extending Python, Files, Errors, and Graphs"],
  },
  {
      name: "ADVANCE TALLY", duration: "1 Months", bgColor: "#d63384",
      topics: ["Accounting & Inventory Masters", "Group & Categories Creation", "Voucher Creation", "Bank Reconciliation", "GST Implement & Return Preparation", "Cost Center Advance Level", "Advance Topics: Voucher Numbering, Voucher Class, Sales/ Purchase Order Processing, BOM, Manufacturing Voucher, Price List etc", "Outstanding Report", "Multiple Currencies", "Inventory Control", "Backup and Restore Data Advance", "GST Setup &IGST, SGST, CGST, CESS", "Email & Cheque Printing Config"],
  },
  {
      name: "ADVANCE Excel", duration: "15 Days", bgColor: "#198754",
      topics: ["Master Microsoft Excel from Beginner to Advanced", "Build a solid understanding on the Basics", "The most common Excel functions", "Harness the full power of Microsoft Excel by automating your day-to-day tasks", "Maintain large sets of Excel data in a list or table", "Create dynamic reports by mastering one of the most popular tools, PivotTables", "Wow your boss by unlocking dynamic formulas with IF, VLOOKUP, INDEX, MATCH functions and many more", "Access to a Professional Trainer with 10+ years of Excel Training"],
  },
  {
      name: "Advance Word", duration: "15 Days", bgColor: "#0d6efd",
      topics: ["You will learn how to take full advantage of Microsoft Word", "Begin with the basics of creating Microsoft Word documents", "Various techniques to create dynamic layouts", "Preparing documents for printing and exporting", "Format documents effectively using Microsoft Word Styles", "Control page formatting and flow with sections and page breaks", "Create and Manage Table Layouts", "Work with Tab Stops to Align Content Properly", "Perform Mail Merges to create Mailing Labels and Form Letters", "Build and Deliver Word Forms", "Manage Templates", "Track and Accept/Reject Changes to a Document", "Access to a Professional Trainer with 10+ years of Excel Training"],
  },
  {
      name: "ADVANCE BASIC", duration: "2 Months", bgColor: "#20c997",
      topics: ["Fundamentals & Hardware Concepts", "Operating System: Windows, Linux", "Microsoft (Word, Excel, PowerPoint)", "Internet Anti Hacking Trick’s", "In death of excel, outlook, ppt", "Practical Tips for Improving Your Typing Speed & Accuracy", "Internet & Basic Online Work’s", "Regional Language Typing (Gujarati, Hindi or any other)"],
  }
];

let htmlStr = '';

courses.forEach((c, idx) => {
    let tColor = c.textColor || "white";
    let bOp = c.bgOpacity || "rgba(255,255,255,0.2)";
    let desc = c.designedFor ? `<li style="margin-bottom: 10px;"><strong>Designed for:</strong><br>${c.designedFor}</li>` : "";
    
    htmlStr += `
          <!-- Course Card ${idx+1}: ${c.name} -->
          <article class="card reveal-scale" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; border: 1px solid #eaeaea; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-radius: 12px;">
            <div style="background-color: ${c.bgColor}; color: ${tColor}; padding: 25px;">
              <h2 style="margin: 0; font-size: 24px; font-weight: 700; color: ${tColor};">${c.name}</h2>
              <span style="display: inline-block; background-color: ${bOp}; padding: 4px 10px; border-radius: 4px; font-size: 14px; margin-top: 10px; font-weight: 600;">⏱️ Duration: ${c.duration}</span>
            </div>
            <div style="padding: 25px; background-color: #ffffff; display: flex; flex-wrap: wrap; gap: 30px;">
              <div style="flex: 1; min-width: 250px;">
                <h3 style="font-size: 18px; color: #212529; margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px;">Focus Areas & Syllabus</h3>
                <ul class="list" style="margin-bottom: 0; color: #495057;">
${c.topics.map(t => '                  <li>' + t + '</li>').join('\n')}
                </ul>
              </div>
              <div style="flex: 1; min-width: 250px;">
                <h3 style="font-size: 18px; color: #212529; margin-top: 0; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px;">Essential Information</h3>
                <ul class="list" style="margin-bottom: 0; color: #495057;">
                  ${desc}
                  <li style="margin-bottom: 10px;"><strong>Time Slot:</strong><br>Any One Hour Between 10:00 AM to 6:00 PM</li>
                  <li><strong>Required Documents for Admission:</strong></li>
                  <ul class="list" style="margin-left: 15px; margin-top: 10px;">
                    <li>ID Proof &amp; Address Proof</li>
                    <li>2 Passport Size Photographs</li>
                  </ul>
                </ul>
              </div>
            </div>
          </article>
`;
});

let content = fs.readFileSync('c:/om/Antigravity/InfinityComputers/InfinityComputer/education.html', 'utf8');
content = content.replace('<!-- Additional course cards will appear here -->', htmlStr);
fs.writeFileSync('c:/om/Antigravity/InfinityComputers/InfinityComputer/education.html', content);
console.log("Successfully mapped 16 courses into the HTML document.");
