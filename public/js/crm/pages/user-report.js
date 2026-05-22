const data = [
  { name:"Hanshraj Yadav",    mgr:"Harshit Saini",  mob:"7470768022", date:"20 May 2026", tc:6,  tcc:5,  tuc:1,  toc:5,  occ:5,  ouc:0,  aod:"00:02:37", tic:1,  icc:0,  iuc:1,  aid:"00:00:00", tdc:3,  dyc:2,  dnc:1,  til:2,  tcl:0,  tll:0,  tfu:9,  asc:"--", acd:"00:02:37", afft:"00:04:23", tct:"00:13:03", tnb:0, tbd:"00:00:00", tws:1, tes:0, tss:0 },
  { name:"Priyanshu Singh",   mgr:"Harshit Saini",  mob:"9201977462", date:"20 May 2026", tc:5,  tcc:3,  tuc:2,  toc:3,  occ:1,  ouc:2,  aod:"00:00:53", tic:2,  icc:2,  iuc:0,  aid:"00:01:02", tdc:1,  dyc:0,  dnc:1,  til:1,  tcl:0,  tll:0,  tfu:15, asc:"--", acd:"00:00:59", afft:"00:01:04", tct:"00:02:56", tnb:0, tbd:"00:00:00", tws:0, tes:0, tss:0 },
  { name:"Sweta Kumari",      mgr:"Shubham Birla",  mob:"9201977470", date:"20 May 2026", tc:55, tcc:17, tuc:38, toc:52, occ:15, ouc:37, aod:"00:00:28", tic:3,  icc:2,  iuc:1,  aid:"00:00:42", tdc:44, dyc:17, dnc:27, til:0,  tcl:0,  tll:16, tfu:37, asc:"--", acd:"00:00:29", afft:"00:00:34", tct:"00:08:16", tnb:0, tbd:"00:00:00", tws:0, tes:0, tss:0 },
  { name:"Kashmira Rudra",    mgr:"Harshit Saini",  mob:"9201977474", date:"20 May 2026", tc:20, tcc:11, tuc:9,  toc:20, occ:11, ouc:9,  aod:"00:00:53", tic:0,  icc:0,  iuc:0,  aid:"00:00:00", tdc:14, dyc:8,  dnc:6,  til:0,  tcl:0,  tll:1,  tfu:12, asc:"--", acd:"00:00:53", afft:"00:00:15", tct:"00:09:43", tnb:0, tbd:"00:00:00", tws:0, tes:0, tss:0 },
  { name:"Muskan Prajapati",  mgr:"Swagat Patra",   mob:"9201977477", date:"20 May 2026", tc:26, tcc:14, tuc:12, toc:23, occ:11, ouc:12, aod:"00:00:32", tic:3,  icc:3,  iuc:0,  aid:"00:00:33", tdc:15, dyc:6,  dnc:9,  til:2,  tcl:0,  tll:4,  tfu:16, asc:"--", acd:"00:00:33", afft:"00:01:07", tct:"00:07:36", tnb:0, tbd:"00:00:00", tws:4, tes:0, tss:0 },
  { name:"Aishee Bansriar",   mgr:"Swagat Patra",   mob:"9201977461", date:"20 May 2026", tc:14, tcc:2,  tuc:12, toc:14, occ:2,  ouc:12, aod:"00:00:34", tic:0,  icc:0,  iuc:0,  aid:"00:00:00", tdc:5,  dyc:0,  dnc:5,  til:4,  tcl:0,  tll:0,  tfu:16, asc:"--", acd:"00:00:34", afft:"00:03:32", tct:"00:01:08", tnb:0, tbd:"00:00:00", tws:0, tes:0, tss:0 },
  { name:"Purva Bhosle",      mgr:"Swagat Patra",   mob:"9201977478", date:"20 May 2026", tc:22, tcc:10, tuc:12, toc:21, occ:9,  ouc:12, aod:"00:01:08", tic:1,  icc:1,  iuc:0,  aid:"00:00:02", tdc:16, dyc:9,  dnc:7,  til:3,  tcl:0,  tll:6,  tfu:24, asc:"--", acd:"00:01:02", afft:"00:00:35", tct:"00:10:16", tnb:0, tbd:"00:00:00", tws:1, tes:0, tss:0 },
  { name:"Niharika Eligeti",  mgr:"Harshit Saini",  mob:"9203390532", date:"20 May 2026", tc:19, tcc:9,  tuc:10, toc:17, occ:7,  ouc:10, aod:"00:01:30", tic:2,  icc:2,  iuc:0,  aid:"00:01:00", tdc:16, dyc:5,  dnc:11, til:3,  tcl:0,  tll:2,  tfu:48, asc:"--", acd:"00:01:24", afft:"00:02:10", tct:"00:12:32", tnb:0, tbd:"00:00:00", tws:0, tes:0, tss:0 },
  { name:"Rupali Rai",        mgr:"Shubham Birla",  mob:"9201977468", date:"20 May 2026", tc:26, tcc:11, tuc:15, toc:25, occ:10, ouc:15, aod:"00:01:24", tic:1,  icc:1,  iuc:0,  aid:"00:00:30", tdc:6,  dyc:1,  dnc:5,  til:3,  tcl:0,  tll:0,  tfu:7,  asc:"--", acd:"00:01:19", afft:"00:00:31", tct:"00:14:30", tnb:0, tbd:"00:00:00", tws:0, tes:0, tss:0 },
  { name:"Ravi Kumar",        mgr:"Swagat Patra",   mob:"9201977466", date:"20 May 2026", tc:30, tcc:9,  tuc:21, toc:29, occ:8,  ouc:21, aod:"00:00:55", tic:1,  icc:1,  iuc:0,  aid:"00:00:44", tdc:26, dyc:13, dnc:13, til:15, tcl:0,  tll:3,  tfu:9,  asc:"--", acd:"00:00:54", afft:"00:00:57", tct:"00:08:04", tnb:0, tbd:"00:00:00", tws:0, tes:0, tss:0 },
];

const tbody = document.getElementById('reportBody');

tbody.innerHTML = data.map((r, i) => `
  <tr>
    <td>${i + 1}</td>
    <td>${r.name}</td>
    <td>${r.mgr}</td>
    <td>${r.mob}</td>
    <td>${r.date}</td>
    <td class="num">${r.tc}</td>
    <td class="num">${r.tcc}</td>
    <td class="num">${r.tuc}</td>
    <td class="num">${r.toc}</td>
    <td class="num">${r.occ}</td>
    <td class="num">${r.ouc}</td>
    <td class="duration">${r.aod}</td>
    <td class="num">${r.tic}</td>
    <td class="num">${r.icc}</td>
    <td class="num">${r.iuc}</td>
    <td class="duration">${r.aid}</td>
    <td class="num">${r.tdc}</td>
    <td class="num">${r.dyc}</td>
    <td class="num">${r.dnc}</td>
    <td class="num">${r.til}</td>
    <td class="num">${r.tcl}</td>
    <td class="num">${r.tll}</td>
    <td class="num">${r.tfu}</td>
    <td class="dash">${r.asc}</td>
    <td class="duration">${r.acd}</td>
    <td class="duration">${r.afft}</td>
    <td class="duration">${r.tct}</td>
    <td class="num">${r.tnb}</td>
    <td class="duration">${r.tbd}</td>
    <td class="num">${r.tws}</td>
    <td class="num">${r.tes}</td>
    <td class="num">${r.tss}</td>
  </tr>
`).join('');
