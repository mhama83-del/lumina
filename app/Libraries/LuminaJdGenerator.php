<?php

namespace App\Libraries;

/**
 * LuminaJdGenerator (Fasa 4B.2)
 * Deterministic synthetic generator for ~1,000 employer job descriptions.
 * Composes JD from curated building blocks (employers x role taxonomy x level
 * x country) while locking the target distribution from the Gemini research.
 * All output is synthetic/paraphrased — no real JD text is copied.
 *
 * Usage: $records = LuminaJdGenerator::generate();  // array of JD records
 */
class LuminaJdGenerator
{
    public const SEED = 20260704;

    /** Domain quotas (total 1,000). */
    private const DOMAIN_QUOTA = ['Business' => 320, 'Engineering' => 300, 'Data' => 250, 'Design' => 130];

    /** Level quotas (total 1,000). */
    private const LEVEL_QUOTA = ['Internship' => 350, 'Fresh Graduate' => 350, 'Graduate Trainee' => 150, 'Junior' => 150];

    /** Country quotas (total 1,000). */
    private const COUNTRY_QUOTA = [
        'Malaysia' => 500, 'Singapore' => 120, 'Indonesia' => 70, 'Thailand' => 70,
        'Vietnam' => 70, 'Philippines' => 70, 'Japan' => 30, 'South Korea' => 25,
        'Taiwan' => 15, 'China' => 15, 'India' => 15,
    ];

    /**
     * Employers: [name, country, city, industry, sector, type, size, domainsCSV]
     * domains: B=Business, E=Engineering, D=Data, G=Design
     */
    private static function employers(): array
    {
        return [
            // ---- Malaysia ----
            ['Maybank','Malaysia','Kuala Lumpur','Finance','Banking','GLC','Large Enterprise','B,D'],
            ['CIMB','Malaysia','Kuala Lumpur','Finance','Banking','Public Listed','Large Enterprise','B,D'],
            ['Public Bank','Malaysia','Kuala Lumpur','Finance','Banking','Public Listed','Large Enterprise','B,D'],
            ['Bank Islam','Malaysia','Kuala Lumpur','Finance','Islamic Banking','Public Listed','Large Enterprise','B,D'],
            ['Bank Simpanan Nasional','Malaysia','Kuala Lumpur','Finance','Islamic Banking','GLC','Large Enterprise','B,D'],
            ['PETRONAS','Malaysia','Kuala Lumpur','Oil & Gas','Energy','GLC','Large Enterprise','E,B,D'],
            ['Tenaga Nasional','Malaysia','Kuala Lumpur','Utility','Energy','GLC','Large Enterprise','E,D'],
            ['Gentari','Malaysia','Kuala Lumpur','Renewable Energy','Green Tech','GLC','Mid-size','E,B'],
            ['Yinson','Malaysia','Kuala Lumpur','Energy','Maritime','Public Listed','Mid-size','E,B'],
            ['Intel Malaysia','Malaysia','Penang','Semiconductor','Manufacturing','MNC','Large Enterprise','E,D'],
            ['Infineon','Malaysia','Melaka','Semiconductor','Manufacturing','MNC','Large Enterprise','E,D'],
            ['Micron','Malaysia','Penang','Semiconductor','Manufacturing','MNC','Large Enterprise','E,D'],
            ['Western Digital','Malaysia','Penang','Semiconductor','Manufacturing','MNC','Large Enterprise','E,D'],
            ['Bosch Malaysia','Malaysia','Penang','Manufacturing','Automation','MNC','Large Enterprise','E'],
            ['Keysight','Malaysia','Penang','Electronics','E&E','MNC','Large Enterprise','E,D'],
            ['ViTrox','Malaysia','Penang','Machine Vision','E&E','Public Listed','Mid-size','E,D'],
            ['Greatech','Malaysia','Penang','Automation','E&E','Public Listed','Mid-size','E'],
            ['Pentamaster','Malaysia','Penang','Automation','E&E','Public Listed','Mid-size','E,D'],
            ['Telekom Malaysia','Malaysia','Kuala Lumpur','Telecommunications','Telco','GLC','Large Enterprise','E,D,B'],
            ['Maxis','Malaysia','Kuala Lumpur','Telecommunications','Telco','Public Listed','Large Enterprise','E,D,B'],
            ['CelcomDigi','Malaysia','Kuala Lumpur','Telecommunications','Telco','Public Listed','Large Enterprise','E,D,B'],
            ['U Mobile','Malaysia','Kuala Lumpur','Telecommunications','Telco','Mid-size','Mid-size','D,B'],
            ['CyberSecurity Malaysia','Malaysia','Cyberjaya','Security','Government Agency','GLC','Mid-size','D'],
            ['Grab Malaysia','Malaysia','Petaling Jaya','Technology','E-commerce','MNC','Large Enterprise','D,B,G'],
            ['Carsome','Malaysia','Petaling Jaya','Technology','E-commerce','Startup','Scaleup','D,B'],
            ['Shopee Malaysia','Malaysia','Kuala Lumpur','Technology','E-commerce','MNC','Large Enterprise','D,B,G'],
            ["Touch 'n Go Digital",'Malaysia','Kuala Lumpur','Fintech','E-wallet','Startup','Scaleup','D,B,G'],
            ['AirAsia','Malaysia','Sepang','Aviation','Airline','Public Listed','Large Enterprise','E,B,D,G'],
            ['Malaysia Airlines','Malaysia','Sepang','Aviation','Airline','GLC','Large Enterprise','E,B'],
            ['Asia Digital Engineering','Malaysia','Sepang','Aviation','MRO','Mid-size','Mid-size','E'],
            ['Gamuda','Malaysia','Petaling Jaya','Construction','Infrastructure','Public Listed','Large Enterprise','E,B'],
            ['IJM','Malaysia','Petaling Jaya','Construction','Infrastructure','Public Listed','Large Enterprise','E,B'],
            ['WCT Holdings','Malaysia','Kuala Lumpur','Construction','Infrastructure','Public Listed','Mid-size','E,B'],
            ['MRT Corp','Malaysia','Kuala Lumpur','Infrastructure','Rail','GLC','Large Enterprise','E,B'],
            ['DHL Malaysia','Malaysia','Shah Alam','Logistics','Supply Chain','MNC','Large Enterprise','B'],
            ['J&T Express','Malaysia','Shah Alam','Logistics','Courier','Mid-size','Mid-size','B'],
            ['Pos Malaysia','Malaysia','Kuala Lumpur','Logistics','Courier','Public Listed','Large Enterprise','B'],
            ['Westports','Malaysia','Port Klang','Maritime','Ports','Public Listed','Large Enterprise','E,B'],
            ['Nestle Malaysia','Malaysia','Petaling Jaya','FMCG','Manufacturing','MNC','Large Enterprise','B,E'],
            ['F&N','Malaysia','Petaling Jaya','FMCG','Manufacturing','Public Listed','Large Enterprise','B,E'],
            ['Top Glove','Malaysia','Klang','Manufacturing','Medical Products','Public Listed','Large Enterprise','E,B'],
            ['Hartalega','Malaysia','Sepang','Manufacturing','Medical Products','Public Listed','Large Enterprise','E,B'],
            ['IHH Healthcare','Malaysia','Kuala Lumpur','Healthcare','Hospital','Public Listed','Large Enterprise','D,B'],
            ['KPJ Healthcare','Malaysia','Kuala Lumpur','Healthcare','Hospital','Public Listed','Large Enterprise','D,B'],
            ['Pharmaniaga','Malaysia','Shah Alam','Pharmaceutical','Manufacturing','Public Listed','Mid-size','E,B,D'],
            ['Duopharma','Malaysia','Klang','Pharmaceutical','Manufacturing','Public Listed','Mid-size','E,B'],
            ['PwC Malaysia','Malaysia','Kuala Lumpur','Consulting','Audit','MNC','Large Enterprise','B,D'],
            ['EY Malaysia','Malaysia','Kuala Lumpur','Consulting','Audit','MNC','Large Enterprise','B,D'],
            ['Deloitte Malaysia','Malaysia','Kuala Lumpur','Consulting','Audit','MNC','Large Enterprise','B,D'],
            ['KPMG Malaysia','Malaysia','Kuala Lumpur','Consulting','Audit','MNC','Large Enterprise','B,D'],
            ['BDO Malaysia','Malaysia','Kuala Lumpur','Consulting','Audit','Mid-size','Mid-size','B,D'],
            ['Media Prima','Malaysia','Petaling Jaya','Media','Broadcasting','Public Listed','Large Enterprise','G,E'],
            ['Astro','Malaysia','Kuala Lumpur','Media','Broadcasting','Public Listed','Large Enterprise','G,D,B'],
            ['MDEC','Malaysia','Cyberjaya','Technology','Government Agency','GLC','Mid-size','B,D'],
            ['Khazanah Nasional','Malaysia','Kuala Lumpur','Investment','Sovereign Fund','GLC','Mid-size','B'],
            ['Kolej Kemahiran Tinggi','Malaysia','Temerloh','Education','TVET','SME','Small Enterprise','E,B'],
            // ---- Singapore ----
            ['DBS Bank','Singapore','Singapore','Finance','Banking','MNC','Large Enterprise','B,D'],
            ['OCBC','Singapore','Singapore','Finance','Banking','MNC','Large Enterprise','B,D'],
            ['UOB','Singapore','Singapore','Finance','Banking','MNC','Large Enterprise','B,D'],
            ['Sea Group','Singapore','Singapore','Technology','E-commerce','MNC','Large Enterprise','D,B,G'],
            ['Singtel','Singapore','Singapore','Telecommunications','Telco','MNC','Large Enterprise','E,D,B'],
            ['ST Engineering','Singapore','Singapore','Aerospace','Defence','Public Listed','Large Enterprise','E,D'],
            ['PSA International','Singapore','Singapore','Maritime','Ports','GLC','Large Enterprise','E,B'],
            ['Changi Airport Group','Singapore','Singapore','Aviation','Airport','GLC','Large Enterprise','E,B,D'],
            // ---- Indonesia ----
            ['GoTo','Indonesia','Jakarta','Technology','E-commerce','MNC','Large Enterprise','D,B,G'],
            ['Tokopedia','Indonesia','Jakarta','Technology','E-commerce','MNC','Large Enterprise','D,B,G'],
            ['Bank Mandiri','Indonesia','Jakarta','Finance','Banking','GLC','Large Enterprise','B,D'],
            ['BCA','Indonesia','Jakarta','Finance','Banking','Public Listed','Large Enterprise','B,D'],
            ['Telkom Indonesia','Indonesia','Bandung','Telecommunications','Telco','GLC','Large Enterprise','E,D,B'],
            ['Traveloka','Indonesia','Jakarta','Technology','Travel Tech','Startup','Scaleup','D,B,G'],
            // ---- Thailand ----
            ['SCG','Thailand','Bangkok','Manufacturing','Materials','Public Listed','Large Enterprise','E,B'],
            ['PTT','Thailand','Bangkok','Oil & Gas','Energy','GLC','Large Enterprise','E,B'],
            ['Agoda','Thailand','Bangkok','Technology','Travel Tech','MNC','Large Enterprise','D,B,G'],
            // ---- Vietnam ----
            ['FPT Software','Vietnam','Hanoi','IT','Software Services','Public Listed','Large Enterprise','E,D'],
            ['Viettel','Vietnam','Hanoi','Telecommunications','Telco','GLC','Large Enterprise','E,D'],
            ['VNG','Vietnam','Ho Chi Minh City','Technology','Gaming','Startup','Scaleup','D,G,B'],
            ['VinGroup','Vietnam','Hanoi','Conglomerate','Automotive','Public Listed','Large Enterprise','E,B'],
            ['MoMo','Vietnam','Ho Chi Minh City','Fintech','E-wallet','Startup','Scaleup','D,B,G'],
            // ---- Philippines ----
            ['Globe Telecom','Philippines','Manila','Telecommunications','Telco','Public Listed','Large Enterprise','E,D,B'],
            ['PLDT','Philippines','Manila','Telecommunications','Telco','Public Listed','Large Enterprise','E,D,B'],
            ['Jollibee Group','Philippines','Manila','F&B','Retail','Public Listed','Large Enterprise','B'],
            ['GCash','Philippines','Manila','Fintech','E-wallet','Startup','Scaleup','D,B,G'],
            // ---- Japan ----
            ['Toyota','Japan','Nagoya','Automotive','Manufacturing','MNC','Large Enterprise','E,G'],
            ['Honda','Japan','Tokyo','Automotive','Manufacturing','MNC','Large Enterprise','E,G'],
            ['Sony','Japan','Tokyo','Electronics','Consumer Tech','MNC','Large Enterprise','E,G,D'],
            ['Panasonic','Japan','Osaka','Electronics','Manufacturing','MNC','Large Enterprise','E,D'],
            ['Rakuten','Japan','Tokyo','Technology','E-commerce','MNC','Large Enterprise','D,B,G'],
            // ---- South Korea ----
            ['Samsung','South Korea','Seoul','Electronics','Semiconductor','MNC','Large Enterprise','E,D'],
            ['LG','South Korea','Seoul','Electronics','Consumer Tech','MNC','Large Enterprise','E,G'],
            ['Naver','South Korea','Seongnam','Technology','Internet','MNC','Large Enterprise','D,B,G'],
            ['SK Hynix','South Korea','Icheon','Semiconductor','Manufacturing','MNC','Large Enterprise','E,D'],
            // ---- Taiwan ----
            ['TSMC','Taiwan','Hsinchu','Semiconductor','Manufacturing','MNC','Large Enterprise','E,D'],
            // ---- China / HK ----
            ['Huawei','China','Shenzhen','Technology','Telco','MNC','Large Enterprise','E,D'],
            ['Alibaba','China','Hangzhou','Technology','E-commerce','MNC','Large Enterprise','D,B,G'],
            ['Lenovo','China','Beijing','Technology','Hardware','MNC','Large Enterprise','E,D'],
            ['HSBC Asia','China','Hong Kong','Finance','Banking','MNC','Large Enterprise','B,D'],
            // ---- India ----
            ['Infosys','India','Bangalore','IT','Software Services','MNC','Large Enterprise','E,D'],
            ['TCS','India','Mumbai','IT','Software Services','MNC','Large Enterprise','E,D'],
            ['Wipro','India','Bangalore','IT','Software Services','MNC','Large Enterprise','E,D'],
            ['Zoho','India','Chennai','Technology','SaaS','Startup','Scaleup','D,B,G'],
        ];
    }

    /** Salary band string by country + level bucket. */
    private static function salaryBand(string $country, string $levelBucket): string
    {
        // [currency, [intern_lo,intern_hi], [fresh_lo,fresh_hi], gradTraineeMult, juniorMult]
        $m = [
            'Malaysia'    => ['RM', [800,1500], [2500,4200], 1.10, 1.20],
            'Singapore'   => ['SGD', [1000,1600], [3800,5200], 1.10, 1.20],
            'Indonesia'   => ['IDR ', [3000000,5000000], [7000000,12000000], 1.15, 1.20],
            'Thailand'    => ['THB ', [10000,15000], [22000,32000], 1.10, 1.20],
            'Vietnam'     => ['VND ', [5000000,8000000], [12000000,18000000], 1.15, 1.20],
            'Philippines' => ['PHP ', [12000,18000], [25000,35000], 1.10, 1.20],
            'Japan'       => ['JPY ', [150000,200000], [230000,300000], 1.10, 1.15],
            'South Korea' => ['KRW ', [1500000,2000000], [2800000,3600000], 1.10, 1.15],
            'Taiwan'      => ['TWD ', [30000,40000], [45000,60000], 1.10, 1.15],
            'China'       => ['CNY ', [4000,6000], [9000,14000], 1.10, 1.15],
            'India'       => ['INR ', [15000,25000], [35000,55000], 1.10, 1.15],
        ];
        $d = $m[$country] ?? $m['Malaysia'];
        [$cur, $intern, $fresh, $gtM, $jrM] = $d;
        if ($levelBucket === 'Internship') { $lo = $intern[0]; $hi = $intern[1]; }
        elseif ($levelBucket === 'Graduate Trainee') { $lo = (int) round($fresh[0] * $gtM); $hi = (int) round($fresh[1] * $gtM); }
        elseif ($levelBucket === 'Junior') { $lo = (int) round($fresh[0] * $jrM); $hi = (int) round($fresh[1] * $jrM); }
        else { $lo = $fresh[0]; $hi = $fresh[1]; }
        $fmt = fn ($n) => number_format($n);
        return trim($cur) . ' ' . $fmt($lo) . ' - ' . $fmt($hi);
    }

    private static function letterToDomain(string $l): string
    {
        return ['B' => 'Business', 'E' => 'Engineering', 'D' => 'Data', 'G' => 'Design'][$l] ?? 'Business';
    }

    /**
     * Role taxonomy. Each family:
     * [domain, req[], pref[], soft[], tools[], kw[], ev[], prog[], pa, sa, ac[], poor, team, vel, cgpa]
     */
    private static function families(): array
    {
        return [
        // ================= DATA =================
        'Data Analyst' => ['Data',['SQL','Data Analysis'],['Python','Dashboarding'],['Attention to Detail','Communication'],['SQL','Excel','Tableau'],['data','analyst','sql','insight','dashboard'],['Evidence of an analytics project turning raw data into a decision','Familiarity with querying real datasets'],['Computer Science','Statistics','Data Science','Mathematics'],'Owl','Fox',['Ant','Octopus'],'Peacock without data evidence may tire of solitary analysis.','Values systematic, evidence-first thinking.','Medium','3.0'],
        'Business Intelligence Analyst' => ['Data',['SQL','Dashboarding'],['Data Modeling','Python'],['Storytelling','Attention to Detail'],['Power BI','SQL','Excel'],['bi','reporting','dashboard','sql','metrics'],['Evidence of building a reporting dashboard','Evidence of translating numbers into a narrative'],['Data Science','Business Analytics','Information Systems'],'Owl','Fox',['Ant','Dolphin'],'Impatient sprinters may skip data validation.','Bridges data and stakeholders.','Medium','3.0'],
        'Data Engineer' => ['Data',['SQL','Python'],['ETL','Cloud'],['Problem Solving','Documentation'],['Python','SQL','Airflow'],['data','pipeline','etl','python','warehouse'],['Evidence of building a data pipeline or ETL job','GitHub repo with data code'],['Computer Science','Software Engineering','Data Science'],'Ant','Owl',['Octopus','Fox'],'Peacock without engineering rigor struggles with pipelines.','Builds reliable data plumbing.','High','3.0'],
        'Machine Learning Engineer' => ['Data',['Python','Machine Learning'],['Statistics','Cloud'],['Analytical Thinking','Curiosity'],['Python','TensorFlow','Git'],['ml','model','python','ai','training'],['Evidence of an ML model built end to end','Kaggle or competition participation'],['Computer Science','Data Science','Artificial Intelligence'],'Owl','Cheetah',['Fox','Ant'],'Loyalists may resist rapid experimentation.','Rewards curiosity and fast iteration.','High','3.2'],
        'AI Engineer' => ['Data',['Python','Machine Learning'],['NLP','Cloud'],['Analytical Thinking','Communication'],['Python','PyTorch','Git'],['ai','ml','python','model','llm'],['Evidence of an applied AI project','Portfolio of models or notebooks'],['Computer Science','Data Science','Artificial Intelligence'],'Owl','Fox',['Cheetah','Ant'],'Rigid profiles struggle with ambiguous AI problems.','Balances research depth with delivery.','High','3.2'],
        'Analytics Trainee' => ['Data',['Data Analysis','Excel'],['SQL','Communication'],['Eagerness to Learn','Teamwork'],['Excel','SQL','Power BI'],['analytics','trainee','data','reporting'],['Evidence of coursework or projects using data','Willingness to learn SQL'],['Business Analytics','Statistics','Economics'],'Owl','Dolphin',['Fox','Ant'],'Overconfident profiles may skip fundamentals.','Supportive learning environment.','Medium','2.8'],
        'Cybersecurity Analyst' => ['Data',['Network Security','Log Analysis'],['SIEM','Python'],['Integrity','Extreme Focus'],['Splunk','Wireshark','Linux'],['cyber','security','soc','threat','siem'],['Evidence of a home lab or CTF participation','Security certification progress (e.g. CEH)'],['Cybersecurity','Computer Science','Information Technology'],'Owl','Ant',['Octopus','Fox'],'Peacock without focus tires of monitoring shifts.','Requires deep, uninterrupted focus.','Medium','3.0'],
        'SOC Analyst' => ['Data',['Intrusion Detection','Log Analysis'],['SIEM','Threat Intelligence'],['Integrity','Vigilance'],['Splunk','SIEM','Linux'],['soc','siem','threat','incident','monitoring'],['Evidence of CTF competitions or personal security lab','Understanding of network protocols'],['Cybersecurity','Computer Science','Information Technology'],'Owl','Ant',['Octopus','Horse'],'Social archetypes struggle with 24/7 monitoring.','Values disciplined incident response.','Medium','3.0'],
        'Cloud Support Analyst' => ['Data',['Cloud','Networking'],['Linux','Scripting'],['Problem Solving','Communication'],['AWS','Linux','Bash'],['cloud','support','aws','infrastructure'],['Evidence of cloud coursework or certification','Hands-on lab with a cloud provider'],['Computer Science','Information Technology','Networking'],'Ant','Owl',['Octopus','Horse'],'Big-picture visionaries may skip operational detail.','Reliable, methodical support work.','Medium','3.0'],
        'Software Engineer' => ['Data',['Software','JavaScript'],['API','Cloud'],['Analytical Thinking','Documentation'],['Git','VS Code','Docker'],['software','developer','code','api','engineer'],['GitHub commit history','Evidence of a full project shipped'],['Computer Science','Software Engineering','Information Technology'],'Octopus','Ant',['Owl','Fox'],'Rigid conformists may resist code reviews.','Ships working software collaboratively.','High','2.8'],
        'Backend Developer' => ['Data',['Software','API'],['SQL','Cloud'],['Analytical Thinking','Documentation'],['Java','Git','Docker'],['backend','api','java','developer','server'],['Strong GitHub commit history','Evidence of building server-side systems'],['Computer Science','Software Engineering','Information Technology'],'Octopus','Ant',['Owl','Fox'],'Purely social profiles struggle with deep coding.','Focus on technical overlap and evidence.','High','2.8'],
        'Frontend Developer' => ['Data',['JavaScript','UI/UX'],['API','Software'],['Creativity','Attention to Detail'],['React','Git','Figma'],['frontend','react','javascript','ui','developer'],['Portfolio of built interfaces','GitHub repos of frontend work'],['Computer Science','Software Engineering','Multimedia'],'Octopus','Dolphin',['Fox','Owl'],'Detail-averse profiles ship rough UIs.','Blends code craft with design sense.','High','2.8'],
        'QA Tester' => ['Data',['Testing','Attention to Detail'],['Automation','SQL'],['Rigor','Communication'],['Selenium','JIRA','Postman'],['qa','testing','quality','automation'],['Evidence of writing test cases','Bug-tracking or QA coursework'],['Computer Science','Information Technology','Software Engineering'],'Ant','Owl',['Horse','Octopus'],'Careless profiles miss defects.','Guards product quality methodically.','Medium','2.8'],
        'IT Support' => ['Data',['Troubleshooting','Networking'],['Hardware','Scripting'],['Patience','Communication'],['Windows','Active Directory','Ticketing'],['it','support','helpdesk','troubleshoot'],['Evidence of resolving technical issues','Hands-on IT coursework'],['Information Technology','Computer Science','Networking'],'Horse','Ant',['Dolphin','Owl'],'Abstract thinkers may dislike routine tickets.','Dependable frontline support.','Low','2.5'],
        'Network Support' => ['Data',['Networking','Routing'],['Security','Linux'],['Patience','Problem Solving'],['Cisco','Wireshark','Linux'],['network','routing','support','cisco'],['CCNA progress or networking labs','Evidence of configuring networks'],['Networking','Information Technology','Computer Science'],'Horse','Ant',['Owl','Octopus'],'Reckless profiles risk network stability.','Structured, safety-first operations.','Medium','2.8'],
        'Systems Administrator' => ['Data',['Linux','Networking'],['Cloud','Scripting'],['Reliability','Problem Solving'],['Linux','Bash','Ansible'],['sysadmin','linux','server','infrastructure'],['Evidence of managing servers or labs','Scripting portfolio'],['Information Technology','Computer Science','Networking'],'Ant','Owl',['Horse','Octopus'],'Impulsive profiles risk uptime.','Keeps systems running reliably.','Medium','3.0'],
        'Product Analyst' => ['Data',['Data Analysis','SQL'],['Product Sense','Communication'],['Curiosity','Storytelling'],['SQL','Amplitude','Excel'],['product','analytics','metrics','data','growth'],['Evidence of analyzing product or user data','A/B test understanding'],['Business Analytics','Computer Science','Economics'],'Fox','Owl',['Dolphin','Octopus'],'Pure introverts may miss stakeholder context.','Connects data to product decisions.','High','3.0'],
        'Digital Transformation Analyst' => ['Data',['Process Analysis','Data Analysis'],['Automation','SQL'],['Adaptability','Communication'],['Power BI','SQL','Visio'],['digital','transformation','process','automation'],['Evidence of improving a process with tech','Cross-functional project work'],['Information Systems','Business Analytics','Computer Science'],'Fox','Eagle',['Owl','Dolphin'],'Rigid profiles resist change work.','Drives change with data.','High','3.0'],
        'Health Data Analyst' => ['Data',['Statistics','Data Analysis'],['R','Research'],['Attention to Detail','Ethics'],['R','SPSS','Excel'],['health','data','biostatistics','analysis'],['Evidence of statistical analysis in health projects','Familiarity with health datasets'],['Health Sciences','Biotechnology','Statistics'],'Owl','Dolphin',['Ant','Fox'],'Careless profiles risk data integrity in health.','Rigorous, ethical data work.','Medium','3.0'],
        'ESG Data Analyst' => ['Data',['Sustainability Reporting','Data Analysis'],['Research','Excel'],['Ethics','Report Writing'],['Excel','Data Visualization','GRI'],['esg','sustainability','data','reporting','carbon'],['Evidence of environmental or green initiatives','Familiarity with GRI/TCFD frameworks'],['Environmental Science','Economics','Business'],'Owl','Dolphin',['Ant','Fox'],'Impatient profiles struggle with meticulous reporting.','Values ethics and evidence.','Medium','3.0'],
        // ================= ENGINEERING =================
        'Mechanical Engineer' => ['Engineering',['Mechanical Design','CAD'],['FEA','Manufacturing'],['Problem Solving','Teamwork'],['SolidWorks','AutoCAD','MATLAB'],['mechanical','design','cad','engineer'],['Final year project involving mechanical design','Evidence of hands-on lab or workshop work'],['Mechanical Engineering','Mechatronics'],'Ant','Octopus',['Owl','Lion'],'Eagle without execution discipline may leave tasks unfinished.','Values tangible, safe deliverables.','Medium','3.0'],
        'Electrical Engineer' => ['Engineering',['Circuit Design','Electrical Schematics'],['PLC','Power Systems'],['Problem Solving','Attention to Detail'],['AutoCAD','MATLAB','PLC'],['electrical','circuit','power','engineer'],['Evidence of circuit or power lab work','Final year project in electrical systems'],['Electrical Engineering','Electronics','Mechatronics'],'Ant','Owl',['Octopus','Horse'],'Reckless profiles risk safety in high-voltage work.','Structured, safety-first engineering.','Medium','3.0'],
        'Civil Engineer' => ['Engineering',['Structural Engineering','Project Planning'],['Quality Control','AutoCAD'],['Toughness','Field Communication'],['AutoCAD Civil 3D','Primavera','MS Project'],['civil','site','structural','construction'],['Evidence of enduring tough site conditions','Physical involvement in engineering events'],['Civil Engineering','Construction Management'],'Wolf','Ant',['Horse','Cheetah'],'Office-bound theorists may struggle on-site.','Thrives outside the office, leads the pack.','Medium','3.0'],
        'Chemical Engineer' => ['Engineering',['Chemical Processing','Process Safety'],['Six Sigma','Aspen HYSYS'],['Attention to Detail','Teamwork'],['Aspen HYSYS','MATLAB','P&ID'],['chemical','process','plant','engineer'],['Evidence of process or lab safety discipline','Final year project in process engineering'],['Chemical Engineering','Process Engineering'],'Ant','Owl',['Horse','Fox'],'Peacock without discipline struggles in a plant.','Strict adherence to process manuals.','Medium','3.0'],
        'Manufacturing Engineer' => ['Engineering',['Manufacturing','Lean'],['Six Sigma','Automation'],['Problem Solving','Teamwork'],['AutoCAD','Minitab','PLC'],['manufacturing','production','lean','engineer'],['Evidence of improving a process or line','Hands-on factory or lab work'],['Mechanical Engineering','Manufacturing Engineering','Mechatronics'],'Ant','Fox',['Octopus','Owl'],'Slow deliberators may stall production goals.','Optimizes lines pragmatically.','Medium','3.0'],
        'Process Engineer' => ['Engineering',['Process Safety','Chemical Processing'],['Six Sigma','Data Analysis'],['Attention to Detail','Collaboration'],['Aspen HYSYS','Minitab','P&ID'],['process','plant','safety','engineer'],['Evidence of plant or process coursework','Lab safety discipline'],['Chemical Engineering','Process Engineering','Mechanical Engineering'],'Ant','Owl',['Fox','Horse'],'Careless profiles risk process safety.','Steady analytical focus.','Medium','3.0'],
        'Automation Engineer' => ['Engineering',['Automation','PLC'],['Robotics','SCADA'],['Problem Solving','Teamwork'],['PLC','SCADA','AutoCAD'],['automation','plc','robotics','engineer'],['Evidence of an automation or robotics project','Hands-on control system work'],['Mechatronics','Electrical Engineering','Mechanical Engineering'],'Ant','Octopus',['Fox','Owl'],'Undisciplined profiles risk equipment.','Builds reliable automated systems.','High','3.0'],
        'Mechatronics Engineer' => ['Engineering',['Robotics','Control Systems'],['PLC','Embedded'],['Problem Solving','Curiosity'],['Arduino','PLC','MATLAB'],['mechatronics','robotics','embedded','engineer'],['Evidence of a robotics or embedded project','Competition or maker portfolio'],['Mechatronics','Electrical Engineering','Robotics'],'Octopus','Ant',['Fox','Owl'],'Rigid profiles resist interdisciplinary work.','Blends mechanical, electrical and code.','High','3.0'],
        'Aerospace Engineer' => ['Engineering',['Aerodynamics','CAD'],['FEA','Fluid Mechanics'],['Precision','Teamwork'],['CATIA','SolidWorks','MATLAB'],['aerospace','aerodynamics','aircraft','engineer'],['Evidence of a UAV or aero project','Final year project in aerospace'],['Aerospace Engineering','Mechanical Engineering'],'Ant','Owl',['Octopus','Fox'],'Careless profiles risk safety-critical work.','Precision and endurance valued.','High','3.2'],
        'Avionics Engineer' => ['Engineering',['Avionics','Electronics'],['Embedded','Testing'],['Precision','Integrity'],['Oscilloscope','MATLAB','Test Rigs'],['avionics','aircraft','electronics','engineer'],['Evidence of avionics or electronics lab work','Understanding of aircraft systems'],['Aerospace Engineering','Electronics','Mechatronics'],'Ant','Owl',['Horse','Octopus'],'Reckless profiles endanger flight systems.','Disciplined, safety-first electronics.','High','3.2'],
        'Aircraft Maintenance Trainee' => ['Engineering',['Aircraft Systems','Maintenance'],['Troubleshooting','Safety'],['Discipline','Teamwork'],['Hand Tools','Test Rigs','Manuals'],['mro','aircraft','maintenance','trainee'],['Evidence of hands-on maintenance work','Aviation coursework or licence progress'],['Aircraft Maintenance','Aeronautical Engineering','TVET'],'Ant','Horse',['Owl','Octopus'],'Impulsive profiles risk aircraft safety.','Strict adherence to maintenance manuals.','Medium','2.8'],
        'Project Engineer' => ['Engineering',['Project Planning','Quality Control'],['AutoCAD','Coordination'],['Leadership','Communication'],['MS Project','AutoCAD','Primavera'],['project','engineer','site','coordination'],['Evidence of coordinating a technical project','Site or field involvement'],['Civil Engineering','Mechanical Engineering','Project Management'],'Ant','Lion',['Octopus','Owl'],'Eagle without execution discipline drifts.','Disciplined execution and coordination.','Medium','3.0'],
        'Site Engineer' => ['Engineering',['Structural Engineering','Site Supervision'],['Quality Control','AutoCAD'],['Toughness','Crisis Management'],['AutoCAD','MS Project','Total Station'],['site','civil','construction','engineer'],['Evidence of enduring field internships','On-site project experience'],['Civil Engineering','Construction Management'],'Wolf','Ant',['Horse','Cheetah'],'Theorists get paralyzed on chaotic sites.','Commands respect on the ground.','Medium','3.0'],
        'QA/QC Engineer' => ['Engineering',['Quality Control','Inspection'],['Six Sigma','Documentation'],['Rigor','Attention to Detail'],['Minitab','Calipers','SPC'],['qaqc','quality','inspection','engineer'],['Evidence of quality or inspection coursework','Attention to measurement detail'],['Mechanical Engineering','Manufacturing Engineering','Quality'],'Ant','Owl',['Horse','Fox'],'Careless profiles pass defects.','Guards quality methodically.','Medium','2.8'],
        'HSE Officer' => ['Engineering',['Safety Management','Risk Assessment'],['Compliance','Auditing'],['Vigilance','Communication'],['MS Office','Checklists','Reporting'],['hse','safety','compliance','officer'],['Evidence of safety training or coursework','Understanding of workplace safety law'],['Occupational Safety','Environmental Engineering','Engineering'],'Horse','Ant',['Owl','Lion'],'Risk-takers undermine safety culture.','Protects people through discipline.','Low','2.8'],
        'Maintenance Engineer' => ['Engineering',['Maintenance','Troubleshooting'],['Reliability','PLC'],['Problem Solving','Reliability'],['CMMS','Hand Tools','PLC'],['maintenance','reliability','engineer','plant'],['Evidence of equipment maintenance work','Hands-on troubleshooting'],['Mechanical Engineering','Electrical Engineering','Mechatronics'],'Ant','Horse',['Octopus','Owl'],'Impatient profiles skip preventive care.','Keeps equipment running reliably.','Medium','2.8'],
        'Field Service Engineer' => ['Engineering',['Troubleshooting','Customer Service'],['Electrical','Mechanical'],['Communication','Adaptability'],['Hand Tools','Diagnostics','CRM'],['field','service','engineer','support'],['Evidence of hands-on field or repair work','Willingness to travel'],['Electrical Engineering','Mechatronics','TVET'],'Horse','Octopus',['Ant','Dolphin'],'Office-only profiles dislike field travel.','Reliable, customer-facing technical work.','Medium','2.8'],
        'CAD Design Engineer' => ['Engineering',['CAD','Mechanical Design'],['FEA','Drafting'],['Attention to Detail','Creativity'],['SolidWorks','AutoCAD','Inventor'],['cad','design','drafting','engineer'],['Portfolio of CAD models or drawings','Final year design project'],['Mechanical Engineering','Product Design','Mechatronics'],'Ant','Octopus',['Owl','Fox'],'Impatient profiles rush drawings.','Precise, detail-oriented design.','Medium','2.8'],
        'Robotics Engineer' => ['Engineering',['Robotics','Control Systems'],['Embedded','IoT'],['Curiosity','Problem Solving'],['ROS','Arduino','Python'],['robotics','iot','embedded','engineer'],['Evidence of a robotics or IoT build','Competition or maker portfolio'],['Robotics','Mechatronics','Electrical Engineering'],'Octopus','Ant',['Fox','Owl'],'Rigid profiles resist experimentation.','Hands-on, iterative building.','High','3.0'],
        'Semiconductor Equipment Engineer' => ['Engineering',['Failure Analysis','Preventive Maintenance'],['Six Sigma','Lean Manufacturing'],['Problem Solving','Attention to Detail'],['AutoCAD','PLCs','JMP'],['semiconductor','equipment','yield','maintenance'],['Evidence of hands-on lab work','Evidence of resolving technical faults'],['Mechanical Engineering','E&E','Mechatronics','Physics'],'Ant','Owl',['Horse','Fox'],'Peacock without discipline struggles with cleanroom rigor.','Analytical precision and operational endurance.','Medium','3.0'],
        'Renewable Energy Engineer' => ['Engineering',['PV System Design','Electrical Schematics'],['Grid Compliance','Project Coordination'],['Adaptability','Negotiation'],['PVSyst','AutoCAD','HOMER'],['solar','renewable','pv','grid','green'],['Final year project in power or solar','Evidence of interest in sustainable tech'],['Electrical Engineering','Renewable Energy','Mechatronics'],'Ant','Eagle',['Fox','Wolf'],'Careless profiles risk grid safety.','Blends technical skill with foresight.','High','3.0'],
        'Marine Operations Trainee' => ['Engineering',['Marine Operations','Safety'],['Logistics','Maintenance'],['Discipline','Teamwork'],['Navigation Systems','Manuals','Radio'],['marine','maritime','operations','trainee'],['Evidence of maritime or logistics coursework','Willingness to work at sea/ports'],['Marine Engineering','Nautical Studies','Logistics'],'Wolf','Ant',['Horse','Octopus'],'Undisciplined profiles risk maritime safety.','Structured, procedure-driven work.','Medium','2.8'],
        'Smart Farming Assistant' => ['Engineering',['IoT','Agronomy'],['Sensors','Data Analysis'],['Curiosity','Adaptability'],['Arduino','Sensors','Excel'],['agriculture','smart','farming','iot','sensor'],['Evidence of a greenhouse or IoT sensor project','Interest in agri-tech'],['Agriculture','Food Technology','Mechatronics'],'Octopus','Ant',['Owl','Fox'],'Rigid office profiles dislike field variability.','Blends agronomy with technical sensing.','Medium','2.8'],
        // ================= BUSINESS =================
        'Management Trainee' => ['Business',['Analytical Thinking','Business Strategy'],['Financial Modeling','Project Management'],['Leadership','Communication','Adaptability'],['Excel','PowerPoint','Power BI'],['management','trainee','leadership','rotation','fast-track'],['Evidence of leadership in clubs or community projects','Evidence of public speaking or leading a team'],['Business','Finance','Accounting','Economics','Law','Engineering'],'Eagle','Lion',['Wolf','Fox'],'Highly rigid conformists (Horse) fail to navigate C-suite ambiguity.','A high-stakes program demanding visionary execution.','High','3.0'],
        'Business Analyst' => ['Business',['Business Analysis','Data Analysis'],['SQL','Process Mapping'],['Communication','Problem Solving'],['Excel','Power BI','Visio'],['business','analyst','requirements','process'],['Evidence of analyzing a business problem','Stakeholder or project coursework'],['Business','Information Systems','Economics'],'Fox','Owl',['Dolphin','Eagle'],'Pure introverts miss stakeholder nuance.','Connects business needs to solutions.','High','3.0'],
        'HR Executive' => ['Business',['Recruitment','HR Operations'],['Employee Relations','HRIS'],['Empathy','Communication'],['Excel','HRIS','LinkedIn'],['hr','recruitment','people','talent'],['Evidence of organizing people or events','Interpersonal or leadership experience'],['Human Resources','Psychology','Business'],'Elephant','Dolphin',['Peacock','Owl'],'Cold, transactional profiles struggle with people work.','Patient, people-first culture.','Medium','2.8'],
        'Talent Acquisition Intern' => ['Business',['Recruitment','Sourcing'],['Interviewing','HRIS'],['Communication','Empathy'],['LinkedIn','Excel','ATS'],['recruitment','talent','sourcing','hr'],['Evidence of networking or people engagement','Communication-heavy activities'],['Human Resources','Psychology','Communication'],'Peacock','Dolphin',['Elephant','Fox'],'Owl without communication evidence struggles here.','Energetic, people-facing role.','Medium','2.8'],
        'Marketing Executive' => ['Business',['Marketing','Content'],['SEO','Social Media'],['Creativity','Communication'],['Canva','Google Analytics','Meta Ads'],['marketing','campaign','brand','content'],['Evidence of running a campaign or content','Portfolio of marketing work'],['Marketing','Communication','Business'],'Peacock','Dolphin',['Fox','Eagle'],'Owl without communication evidence dampens conversion.','Rewards persuasion and network-building.','Medium','2.8'],
        'Digital Marketing Executive' => ['Business',['SEO','Social Media'],['Marketing','Data Analysis'],['Creativity','Communication'],['Google Analytics','Meta Ads','Canva'],['digital','marketing','seo','social','ads'],['Evidence of managing social or ad campaigns','Metrics-backed marketing results'],['Marketing','Communication','Business'],'Peacock','Fox',['Dolphin','Cheetah'],'Data-averse profiles miss campaign optimization.','Blends creativity with performance data.','High','2.8'],
        'Sales Executive' => ['Business',['Sales','Communication'],['Negotiation','CRM'],['Persuasion','Resilience'],['CRM','Excel','LinkedIn'],['sales','business','client','revenue'],['Evidence of persuading or selling','Customer-facing experience'],['Business','Marketing','Communication'],'Lion','Peacock',['Cheetah','Fox'],'Shy, conflict-averse profiles struggle to close.','Drives revenue with confidence.','Medium','2.5'],
        'Business Development Executive' => ['Business',['Business Development','Negotiation'],['Market Research','CRM'],['Persuasion','Strategic Thinking'],['CRM','Excel','LinkedIn'],['business','development','partnership','growth'],['Evidence of building relationships or deals','Initiative in projects or ventures'],['Business','Marketing','Health Sciences'],'Eagle','Peacock',['Fox','Lion'],'Passive profiles miss growth opportunities.','Proactive, opportunity-hunting role.','High','2.8'],
        'Customer Success Associate' => ['Business',['Customer Service','Communication'],['CRM','Onboarding'],['Empathy','Patience'],['CRM','Zendesk','Excel'],['customer','success','support','retention'],['Evidence of helping or supporting people','Service-oriented experience'],['Business','Communication','Psychology'],'Dolphin','Elephant',['Peacock','Horse'],'Impatient profiles frustrate customers.','Empathetic, relationship-driven work.','Medium','2.5'],
        'Operations Executive' => ['Business',['Operations','Process Management'],['Data Analysis','Coordination'],['Organization','Problem Solving'],['Excel','ERP','Power BI'],['operations','process','coordination','logistics'],['Evidence of organizing operations or events','Process improvement examples'],['Business','Operations Management','Logistics'],'Wolf','Ant',['Horse','Fox'],'Purely theoretical profiles stall real operations.','Keeps the engine running smoothly.','Medium','2.8'],
        'Supply Chain Executive' => ['Business',['Supply Chain','Data Analysis'],['SQL','Procurement'],['Organization','Analytical Thinking'],['Excel','SAP','Power BI'],['supply','chain','logistics','procurement'],['Evidence of supply chain or logistics coursework','Data-driven process work'],['Logistics','Supply Chain','Business'],'Fox','Ant',['Owl','Wolf'],'Disorganized profiles break the chain.','Optimizes flow with data.','Medium','2.8'],
        'Logistics Coordinator' => ['Business',['Logistics','Coordination'],['Supply Chain','Excel'],['Organization','Crisis Management'],['Excel','WMS','ERP'],['logistics','coordination','hub','delivery'],['Evidence of coordinating deliveries or events','Organizational experience'],['Logistics','Supply Chain','Business'],'Wolf','Cheetah',['Horse','Ant'],'Theoretical profiles freeze under warehouse pace.','Fast, decisive coordination.','Low','2.5'],
        'Procurement Executive' => ['Business',['Procurement','Negotiation'],['Vendor Management','Excel'],['Analytical Thinking','Communication'],['Excel','SAP','ERP'],['procurement','vendor','sourcing','purchasing'],['Evidence of negotiation or sourcing','Cost-analysis coursework'],['Business','Supply Chain','Economics'],'Fox','Owl',['Ant','Dolphin'],'Impulsive profiles overspend.','Analytical, negotiation-savvy work.','Medium','2.8'],
        'Finance Executive' => ['Business',['Financial Analysis','Excel'],['Accounting','Modeling'],['Attention to Detail','Integrity'],['Excel','SAP','Power BI'],['finance','financial','analysis','budget'],['Evidence of financial or quantitative projects','Strong numeracy'],['Finance','Accounting','Economics'],'Owl','Ant',['Fox','Horse'],'Careless profiles risk financial errors.','Rigorous, numbers-first work.','Medium','3.0'],
        'Accounting Associate' => ['Business',['Accounting','Excel'],['IFRS','Bookkeeping'],['Attention to Detail','Integrity'],['Excel','MYOB','SAP'],['accounting','bookkeeping','ledger','finance'],['Evidence of accounting coursework or projects','Familiarity with financial statements'],['Accounting','Finance'],'Owl','Ant',['Horse','Fox'],'Careless profiles produce errors.','Meticulous, disciplined accounting.','Medium','3.0'],
        'Audit Associate' => ['Business',['Auditing','Accounting'],['IFRS','Forensic Analysis'],['Attention to Detail','Integrity'],['Excel','Audit Software','SAP'],['audit','assurance','accounting','compliance'],['Evidence of understanding accounting procedures','Analytical rigor in projects'],['Accounting','Finance'],'Owl','Ant',['Horse','Fox'],'Peacock without documentation discipline fails audit SLAs.','Scholarly attention to detail.','High','3.0'],
        'Tax Associate' => ['Business',['Taxation','Accounting'],['IFRS','Compliance'],['Attention to Detail','Integrity'],['Excel','Tax Software','SAP'],['tax','compliance','accounting','associate'],['Evidence of tax or accounting coursework','Analytical, detail-heavy work'],['Accounting','Finance','Law'],'Owl','Ant',['Fox','Horse'],'Sloppy profiles risk compliance errors.','Precise, regulation-driven work.','Medium','3.0'],
        'Risk Analyst' => ['Business',['Risk Analysis','Data Analysis'],['Financial Modeling','SQL'],['Attention to Detail','Objectivity'],['Excel','Python','Power BI'],['risk','analysis','credit','compliance'],['Evidence of strong quantitative analysis','Familiarity with large datasets'],['Finance','Economics','Data Science'],'Owl','Ant',['Fox','Horse'],'Overly optimistic profiles miss risks.','Systematic, objective risk work.','High','3.0'],
        'Compliance Executive' => ['Business',['Compliance','Regulatory Analysis'],['KYC/AML','Documentation'],['Integrity','Attention to Detail'],['Excel','Compliance Tools','MS Office'],['compliance','regulatory','kyc','governance'],['Evidence of interpreting rules or documents','Moot court or debate for law students'],['Law','Business','Islamic Studies'],'Owl','Ant',['Horse','Fox'],'Careless profiles create regulatory risk.','Disciplined, rule-driven work.','Medium','3.0'],
        'Legal Associate' => ['Business',['Legal Research','Document Analysis'],['Contract Review','Compliance'],['Analytical Thinking','Integrity'],['MS Word','Legal Databases','Excel'],['legal','law','contract','compliance'],['Evidence of moot court or legal writing','Strong analytical argument skills'],['Law','Syariah','Political Science'],'Owl','Fox',['Ant','Elephant'],'Impulsive profiles miss legal nuance.','Rigorous interpretation and argument.','Medium','3.0'],
        'Syariah Compliance Associate' => ['Business',['Syariah Compliance','Regulatory Analysis'],['Islamic Finance','Documentation'],['Integrity','Communication'],['MS Office','Compliance Tools','Excel'],['syariah','islamic','compliance','banking'],['Evidence of Islamic finance or Syariah coursework','Document interpretation skills'],['Islamic Studies','Syariah','Law','Islamic Banking'],'Owl','Elephant',['Ant','Horse'],'Careless profiles risk Syariah non-compliance.','Principled, documentation-first work.','Medium','3.0'],
        'Project Coordinator' => ['Business',['Project Coordination','Communication'],['Scheduling','Documentation'],['Organization','Communication'],['MS Project','Excel','Trello'],['project','coordination','schedule','pmo'],['Evidence of coordinating a project or event','Organizational leadership'],['Business','Project Management','Communication'],'Wolf','Dolphin',['Ant','Fox'],'Disorganized profiles miss deadlines.','Keeps projects on track.','Medium','2.8'],
        'Event Executive' => ['Business',['Event Management','Coordination'],['Marketing','Vendor Management'],['Energy','Communication'],['Excel','Canva','Trello'],['event','coordination','marketing','logistics'],['Evidence of organizing events','Vendor or stakeholder coordination'],['Communication','Business','Tourism'],'Peacock','Wolf',['Dolphin','Cheetah'],'Introverted profiles tire in high-energy events.','Lively, coordination-heavy role.','Medium','2.5'],
        'Hospitality Management Trainee' => ['Business',['Hospitality Operations','Customer Service'],['F&B','Front Office'],['Empathy','Adaptability'],['POS','Excel','PMS'],['hospitality','hotel','service','trainee'],['Evidence of service or hospitality work','Customer-facing experience'],['Hospitality','Tourism','Business'],'Dolphin','Peacock',['Elephant','Wolf'],'Impersonal profiles struggle with guest care.','Warm, service-first culture.','Medium','2.5'],
        'Education Programme Executive' => ['Business',['Programme Management','Communication'],['Curriculum Support','Coordination'],['Empathy','Organization'],['MS Office','LMS','Excel'],['education','programme','training','coordination'],['Evidence of teaching or mentoring','Programme or event coordination'],['Education','Social Sciences','Communication'],'Elephant','Dolphin',['Wolf','Owl'],'Impatient profiles struggle with learner support.','Patient, development-focused work.','Low','2.8'],
        'Sustainability ESG Associate' => ['Business',['Sustainability Reporting','Research'],['Data Analysis','Compliance'],['Ethics','Communication'],['Excel','GRI','Power BI'],['esg','sustainability','associate','reporting'],['Evidence of green or NGO involvement','Familiarity with ESG frameworks'],['Environmental Science','Business','Economics'],'Owl','Dolphin',['Ant','Eagle'],'Impatient profiles skip meticulous reporting.','Ethics and evidence valued.','Medium','3.0'],
        // ================= DESIGN =================
        'UX Designer' => ['Design',['UI/UX','Wireframing'],['Prototyping','User Research'],['Empathy','Creativity'],['Figma','Sketch','Miro'],['ux','design','wireframe','research','figma'],['A portfolio showing a UX case study','Evidence of real user interviews or surveys'],['Design','Human-Computer Interaction','Psychology'],'Octopus','Dolphin',['Peacock','Owl'],'Commanding types enforce bias over user feedback.','Deep empathy plus practical building.','Medium','2.8'],
        'UI Designer' => ['Design',['UI/UX','Visual Design'],['Prototyping','Design Systems'],['Attention to Detail','Creativity'],['Figma','Illustrator','Sketch'],['ui','design','visual','figma','interface'],['Portfolio of polished interfaces','Evidence of design system work'],['Design','Multimedia','Human-Computer Interaction'],'Octopus','Peacock',['Dolphin','Owl'],'Detail-averse profiles ship rough UI.','Craft and consistency valued.','Medium','2.8'],
        'Product Designer' => ['Design',['UI/UX','Prototyping'],['User Research','Design Systems'],['Problem Solving','Creativity'],['Figma','Behance','Miro'],['product','design','ux','prototype','figma'],['A Figma/Behance portfolio of UI detail','Evidence of iterative design improvement'],['Design','Human-Computer Interaction','Multimedia'],'Octopus','Eagle',['Dolphin','Peacock'],'Rigid profiles resist iterative design.','Iterative problem-solving valued.','High','3.0'],
        'Graphic Designer' => ['Design',['Graphic Design','Typography'],['Branding','Illustration'],['Creativity','Attention to Detail'],['Photoshop','Illustrator','InDesign'],['graphic','design','branding','visual'],['A design portfolio (Behance/Dribbble)','Evidence of client or campaign work'],['Graphic Design','Multimedia','Fine Arts'],'Peacock','Octopus',['Dolphin','Fox'],'Rigid profiles limit creative range.','Creativity with production discipline.','Medium','2.5'],
        'Brand Executive' => ['Business',['Branding','Marketing'],['Content','Social Media'],['Creativity','Communication'],['Canva','Photoshop','Analytics'],['brand','marketing','identity','campaign'],['Evidence of brand or campaign work','Portfolio of creative outputs'],['Marketing','Communication','Design'],'Peacock','Dolphin',['Fox','Eagle'],'Introverted profiles struggle with brand voice.','Expressive, audience-facing role.','Medium','2.8'],
        'Content Creator' => ['Design',['Content','Writing'],['Social Media','Video Editing'],['Creativity','Communication'],['Canva','Premiere Pro','CapCut'],['content','creator','social','video','writing'],['A portfolio of content or social posts','Evidence of audience growth'],['Communication','Mass Communication','Creative Writing'],'Peacock','Dolphin',['Fox','Octopus'],'Rigid profiles struggle with fast content cycles.','Expressive, fast-moving creativity.','Medium','2.5'],
        'Social Media Executive' => ['Design',['Social Media','Content'],['SEO','Analytics'],['Creativity','Communication'],['Meta Business','Canva','Analytics'],['social','media','content','engagement'],['Evidence of managing social accounts','Growth or engagement metrics'],['Communication','Marketing','Multimedia'],'Peacock','Fox',['Dolphin','Cheetah'],'Data-blind profiles miss engagement signals.','Creative plus metrics-aware.','Medium','2.5'],
        'Video Editor' => ['Design',['Video Editing','Storytelling'],['Motion Graphics','Color Grading'],['Creativity','Time Management'],['Premiere Pro','After Effects','DaVinci'],['video','editing','post','motion'],['A showreel of edited videos','Evidence of meeting tight deadlines'],['Multimedia','Film','Broadcasting'],'Octopus','Peacock',['Dolphin','Fox'],'Disorganized profiles miss deadlines.','Craft under deadline pressure.','Medium','2.5'],
        'Motion Designer' => ['Design',['Motion Graphics','Animation'],['Video Editing','Typography'],['Creativity','Time Management'],['After Effects','Premiere Pro','Illustrator'],['motion','animation','graphics','video'],['A portfolio demonstrating fluid animation','Understanding of pacing and timing'],['Multimedia Design','Animation','Graphic Design'],'Peacock','Octopus',['Dolphin','Fox'],'Rigid profiles limit motion creativity.','Creative craft under deadlines.','Medium','2.5'],
        'Creative Strategist' => ['Design',['Creative Strategy','Branding'],['Marketing','Storytelling'],['Creativity','Communication'],['Miro','Canva','Analytics'],['creative','strategy','brand','campaign'],['A portfolio of concepts or campaigns','Evidence of strategic thinking'],['Communication','Marketing','Design'],'Eagle','Peacock',['Fox','Dolphin'],'Execution-only profiles lack big-picture vision.','Ideas plus audience insight.','High','3.0'],
        'Digital Media Executive' => ['Design',['Digital Media','Content'],['Social Media','Analytics'],['Creativity','Communication'],['Canva','Premiere Pro','Analytics'],['digital','media','content','creative'],['Portfolio of digital media work','Evidence of audience engagement'],['Multimedia','Mass Communication','Marketing'],'Peacock','Fox',['Dolphin','Octopus'],'Introverted profiles struggle with audience work.','Expressive, media-savvy role.','Medium','2.5'],
        'Instructional Designer' => ['Design',['Instructional Design','Content'],['E-Learning','Storyboarding'],['Empathy','Organization'],['Articulate','Canva','LMS'],['instructional','elearning','curriculum','design'],['Evidence of creating learning materials','Teaching or training experience'],['Education','Multimedia','Instructional Technology'],'Elephant','Octopus',['Dolphin','Owl'],'Impatient profiles skip learner needs.','Patient, learner-centered design.','Medium','2.8'],
        'Architecture Assistant' => ['Design',['Architectural Design','CAD'],['3D Modeling','Rendering'],['Creativity','Attention to Detail'],['AutoCAD','Revit','SketchUp'],['architecture','design','cad','building'],['A design portfolio of architectural work','Evidence of model or drawing skill'],['Architecture','Built Environment','Design'],'Octopus','Ant',['Owl','Peacock'],'Careless profiles produce inaccurate plans.','Creative craft with technical precision.','Medium','2.8'],
        'Interior Design Assistant' => ['Design',['Interior Design','CAD'],['3D Rendering','Space Planning'],['Creativity','Attention to Detail'],['AutoCAD','SketchUp','Photoshop'],['interior','design','space','rendering'],['A portfolio of interior projects','Evidence of client or brief work'],['Interior Design','Architecture','Design'],'Octopus','Peacock',['Dolphin','Ant'],'Rigid profiles limit spatial creativity.','Creativity with client sensitivity.','Medium','2.5'],
        'Multimedia Designer' => ['Design',['Multimedia','Graphic Design'],['Video Editing','Animation'],['Creativity','Time Management'],['Photoshop','After Effects','Illustrator'],['multimedia','design','graphic','video'],['A multimedia portfolio','Evidence of varied creative outputs'],['Multimedia','Graphic Design','Creative Media'],'Octopus','Peacock',['Dolphin','Fox'],'Narrow profiles miss multimedia range.','Versatile, hands-on creativity.','Medium','2.5'],
        'Game Designer' => ['Design',['Game Design','Prototyping'],['UX','Storytelling'],['Creativity','Problem Solving'],['Unity','Figma','Photoshop'],['game','design','interactive','prototype'],['A portfolio of game or interactive projects','Evidence of playtesting or iteration'],['Game Design','Multimedia','Computer Science'],'Octopus','Fox',['Peacock','Owl'],'Rigid profiles resist playful iteration.','Iterative, player-focused design.','High','2.8'],
        ];
    }

    // ---------------- helpers ----------------

    private static function slug(string $s): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($s))), '_');
    }

    private static function skill(string $name, string $cat, string $imp, float $w): array
    {
        return ['skill_name' => $name, 'skill_code' => self::slug($name), 'skill_category' => $cat, 'importance' => $imp, 'weight' => $w];
    }

    private static function region(string $country): string
    {
        $ea = ['Japan', 'South Korea', 'Taiwan', 'China'];
        if (in_array($country, $ea, true)) return 'East Asia';
        if ($country === 'India') return 'South Asia';
        return 'Southeast Asia';
    }

    private static function resolveLevel(string $bucket, string $domain): string
    {
        if ($bucket !== 'Junior') return $bucket;
        return $domain === 'Engineering' ? 'Junior Engineer' : ($domain === 'Data' ? 'Junior Analyst' : 'Junior Executive');
    }

    private static function title(string $family, string $bucket): string
    {
        if ($bucket === 'Internship')       return $family . ' Intern';
        if ($bucket === 'Graduate Trainee') return str_contains($family, 'Trainee') ? $family : $family . ' Trainee';
        if ($bucket === 'Junior')           return str_contains($family, 'Trainee') ? $family : 'Junior ' . $family;
        return $family; // Fresh Graduate
    }

    private static function arrangement(string $domain, string $bucket): string
    {
        if ($domain === 'Engineering') return mt_rand(0, 9) < 8 ? 'On-site' : 'Hybrid';
        if ($bucket === 'Internship')  return mt_rand(0, 9) < 6 ? 'On-site' : 'Hybrid';
        $r = mt_rand(0, 9);
        return $r < 5 ? 'Hybrid' : ($r < 8 ? 'On-site' : 'Remote');
    }

    private static function bag(array $quota): array
    {
        $out = [];
        foreach ($quota as $k => $n) { for ($i = 0; $i < $n; $i++) $out[] = $k; }
        return $out;
    }

    private static function shuffleDet(array &$a): void
    {
        for ($i = count($a) - 1; $i > 0; $i--) { $j = mt_rand(0, $i); [$a[$i], $a[$j]] = [$a[$j], $a[$i]]; }
    }

    private static function summary(string $family, string $company, string $domain, string $bucket, array $f): string
    {
        $lvl = ['Internship' => 'hands-on internship', 'Fresh Graduate' => 'fresh-graduate role', 'Graduate Trainee' => 'structured graduate programme', 'Junior' => 'early-career role'][$bucket] ?? 'role';
        $verb = ['Data' => 'turn data into decisions', 'Engineering' => 'build and maintain real systems safely', 'Design' => 'craft user-centred experiences', 'Business' => 'drive operations and growth'][$domain];
        $t = [
            "A {$lvl} at {$company} to {$verb} as a {$family}.",
            "Join {$company} as a {$family} — a {$lvl} focused on helping the team {$verb}.",
            "{$company} is growing its {$domain} team: a {$lvl} where you {$verb}.",
        ];
        return $t[mt_rand(0, 2)];
    }

    private static function responsibilities(array $f, string $domain): array
    {
        $req = $f[1]; $tools = $f[4]; $kw = $f[5];
        return array_values(array_filter([
            'Apply ' . ($req[0] ?? $domain . ' skills') . ' to everyday tasks.',
            isset($req[1]) ? 'Support work involving ' . $req[1] . '.' : 'Support the wider team on live projects.',
            isset($tools[0]) ? 'Use ' . implode(', ', array_slice($tools, 0, 2)) . ' to deliver outcomes.' : null,
            'Collaborate with the team, document your work, and grow ' . ($kw[0] ?? $domain) . ' capabilities.',
        ]));
    }

    private static function jdText(array $e, string $family, string $bucket, string $domain, array $f, string $title): string
    {
        $company = $e[0]; $type = $e[5];
        $tone = in_array($type, ['Startup', 'Scaleup'], true)
            ? "Move fast and learn faster"
            : (in_array($type, ['GLC', 'MNC', 'Large Enterprise'], true) ? "Grow within a structured, established team" : "Make a real impact in a focused team");
        $sk = implode(', ', array_slice($f[1], 0, 2));
        $ev = $f[6][0] ?? 'Show evidence of relevant projects.';
        return "{$title} at {$company}. {$tone}. "
             . "This {$bucket} role sits in the {$domain} domain and looks for strength in {$sk}. "
             . "What matters most: {$ev} "
             . "(Synthetic listing generated for Lumina matching; not a real advertisement.)";
    }

    /** Main generator — returns ~1,000 JD records. */
    public static function generate(): array
    {
        $employers = self::employers();
        $families  = self::families();

        $famByDomain = ['Data' => [], 'Engineering' => [], 'Design' => [], 'Business' => []];
        foreach ($families as $name => $f) { $famByDomain[$f[0]][] = $name; }

        $empByCountry = [];
        $empByCountryDomain = [];
        foreach ($employers as $i => $e) {
            $country = $e[1];
            $empByCountry[$country][] = $i;
            foreach (explode(',', $e[7]) as $dl) {
                $d = self::letterToDomain(trim($dl));
                $empByCountryDomain[$country][$d][] = $i;
            }
        }

        $domainSlots  = self::bag(self::DOMAIN_QUOTA);
        $levelSlots   = self::bag(self::LEVEL_QUOTA);
        $countrySlots = self::bag(self::COUNTRY_QUOTA);
        mt_srand(self::SEED);     self::shuffleDet($domainSlots);
        mt_srand(self::SEED + 1); self::shuffleDet($levelSlots);
        mt_srand(self::SEED + 2); self::shuffleDet($countrySlots);
        mt_srand(self::SEED + 7); // per-record variation

        $total = min(count($domainSlots), count($levelSlots), count($countrySlots));
        $famCounter = ['Data' => 0, 'Engineering' => 0, 'Design' => 0, 'Business' => 0];
        $empCounter = [];
        $records = [];

        for ($k = 0; $k < $total; $k++) {
            $domain  = $domainSlots[$k];
            $bucket  = $levelSlots[$k];
            $country = $countrySlots[$k];

            $fams    = $famByDomain[$domain];
            $famName = $fams[$famCounter[$domain] % count($fams)];
            $famCounter[$domain]++;
            $f = $families[$famName];

            $cands = $empByCountryDomain[$country][$domain] ?? ($empByCountry[$country] ?? [0]);
            $ckey  = $country . '|' . $domain;
            $empCounter[$ckey] = $empCounter[$ckey] ?? 0;
            $e = $employers[$cands[$empCounter[$ckey] % count($cands)]];
            $empCounter[$ckey]++;

            $roleLevel   = self::resolveLevel($bucket, $domain);
            $title       = self::title($famName, $bucket);
            $salary      = self::salaryBand($country, $bucket);
            $arrangement = self::arrangement($domain, $bucket);
            $jdCode      = sprintf('JD%04d', $k + 1);

            $skills = [];
            foreach ($f[1] as $s) $skills[] = self::skill($s, 'Technical', 'required', 1.50);
            foreach ($f[2] as $s) $skills[] = self::skill($s, 'Technical', 'preferred', 0.80);
            foreach ($f[3] as $s) $skills[] = self::skill($s, 'Soft Skill', 'required', 1.00);
            foreach ($f[4] as $s) $skills[] = self::skill($s, 'Tool', 'preferred', 0.70);

            $cgpa = ($domain === 'Design') ? 'N/A' : $f[14];

            $records[] = [
                'employer' => [
                    'company_name' => $e[0], 'country' => $e[1], 'city' => $e[2],
                    'industry' => $e[3], 'sector' => $e[4], 'company_type' => $e[5], 'company_size' => $e[6],
                ],
                'role' => [
                    'jd_code' => $jdCode, 'role_title' => $title, 'role_family' => $famName,
                    'role_level' => $roleLevel, 'target_domain' => $domain, 'availability_type' => $roleLevel,
                    'work_arrangement' => $arrangement, 'location_country' => $country, 'location_city' => $e[2],
                    'region' => self::region($country), 'salary_band' => $salary,
                    'suitable_programmes' => $f[7], 'role_summary' => self::summary($famName, $e[0], $domain, $bucket, $f),
                    'responsibilities' => self::responsibilities($f, $domain),
                    'keywords' => $f[5], 'evidence_required' => $f[6],
                    'learning_velocity_need' => $f[13], 'minimum_cgpa_category' => $cgpa,
                    'match_weighting' => ['Skill' => 40, 'Evidence' => 20, 'Trajectory' => 20, 'Animal' => 10, 'Domain' => 5, 'Academic' => 5],
                    'source_reference' => 'Gemini Research + Synthetic Lumina JD Generation',
                    'synthetic_jd_text' => self::jdText($e, $famName, $bucket, $domain, $f, $title),
                    'notes_for_lumina_matching' => 'Weight technical overlap and evidence; ideal Work Animal: ' . $f[8] . ' / ' . $f[9] . '.',
                ],
                'skills' => $skills,
                'animal' => [
                    'preferred_primary_animal' => $f[8], 'preferred_secondary_animal' => $f[9],
                    'acceptable' => $f[10], 'poor_fit_risk' => $f[11], 'team_fit_note' => $f[12],
                ],
            ];
        }
        return $records;
    }
}
