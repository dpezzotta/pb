<!doctype html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- PAGE SEO -->
<title>Staff Bio - PlanetBravo's STEM Camps</title>
<meta name="description" content="Meet the staff of PlanetBravo's award-winning summer tech camp.">
<!-- END PAGE SEO -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header1.php'; ?>
<!-- BEGIN CUSTOM STYLES -->
<link rel="stylesheet" href="/css/stylesheets/pages/camps/locations.css" />
<style>
.pb-bio-focused a img {
  border-radius: 50%;
  box-shadow: 0 0 0 7px #ff9700, 0 0 28px rgba(255,151,0,.95);
}
.pb-bio-location a img {
  border-radius: 50%;
  box-shadow: 0 0 0 4px #2f80c7, 0 0 15px rgba(47,128,199,.45);
}
.pb-bio-focused dt {
  color: #102b4f;
  font-weight: 900;
}
.pb-bio-focused > div {
  margin-bottom: 14px;
}
.pb-bio-location > div {
  margin-bottom: 8px;
}
.pb-bio-section-heading {
  clear: both;
  width: 100% !important;
  padding: 12px 0 18px;
  text-align: center;
}
.pb-bio-section-heading h2 {
  margin: 0 0 4px;
  color: #102b4f;
  font-size: 30px;
  line-height: 36px;
  font-weight: 900;
}
.pb-bio-section-heading.pb-bio-rest-heading {
  margin-top: 20px;
  padding-top: 28px;
  border-top: 1px solid #dbe6f0;
}
</style>
<!-- END CUSTOM STYLES -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header2.php'; ?>
<!-- BEGIN BODY CONTENT -->

<?php
function pb_bio_person($name, $camp, $image, $bio, $needs_update = false) {
  return array(
    'name' => $name,
    'camp' => $camp,
    'image' => $image,
    'bio' => $bio,
    'needs_update' => $needs_update
  );
}

function pb_bio_image_src($image) {
  if (strpos($image, '/') === 0) return $image;
  return '/img/staff-img/2026/' . $image;
}

function pb_bio_normalize_name($name) {
  $name = strtolower(trim((string)$name));
  $name = preg_replace('/[^a-z0-9]+/', '', $name);
  return $name;
}

function pb_bio_decode_staff_token() {
  if (empty($_GET['pb_staff'])) {
    return array('loc' => array(), 'hi' => array(), 'dir' => array());
  }

  $token = preg_replace('/[^A-Za-z0-9\-_]/', '', (string)$_GET['pb_staff']);
  if ($token == '') {
    return array('loc' => array(), 'hi' => array(), 'dir' => array());
  }

  $padded = strtr($token, '-_', '+/');
  $padding = strlen($padded) % 4;
  if ($padding) {
    $padded .= str_repeat('=', 4 - $padding);
  }

  $json = base64_decode($padded, true);
  if ($json === false) {
    return array('loc' => array(), 'hi' => array(), 'dir' => array());
  }

  $data = json_decode($json, true);
  if (!is_array($data)) {
    return array('loc' => array(), 'hi' => array(), 'dir' => array());
  }

  $location = !empty($data['loc']) && is_array($data['loc']) ? $data['loc'] : array();
  $highlight = !empty($data['hi']) && is_array($data['hi']) ? $data['hi'] : array();
  $director = !empty($data['dir']) && is_array($data['dir']) ? $data['dir'] : array();
  return array('loc' => pb_bio_name_lookup($location), 'hi' => pb_bio_name_lookup($highlight), 'dir' => pb_bio_name_lookup($director));
}

function pb_bio_name_lookup($names) {
  $lookup = array();
  $order = 1;
  foreach ($names as $name) {
    $key = pb_bio_normalize_name($name);
    if ($key != '' && empty($lookup[$key])) {
      $lookup[$key] = $order++;
    }
  }
  return $lookup;
}

$pb_bio_staff_token = pb_bio_decode_staff_token();
$pb_bio_has_staff_token = count($pb_bio_staff_token['loc']) || count($pb_bio_staff_token['hi']) || count($pb_bio_staff_token['dir']);

$staff = array(
  pb_bio_person('Henry "Arcade" Petrie', 'Arcade', '/img/staff-img/2025/arcade-cir.png', <<<'BIO'
Returning again for the second year, Arcade is back in action and ready to have some summer fun! When he's not drawing, making music, animating, or creating games, Arcade is a video game aficionado, true to his namesake! Outside of that, Arcade enjoys watching movies, worldbuilding, and just generally being cool. Arcade only talks about Arcade in the third person. Arcade is just awesome like that.
BIO
  ),
  pb_bio_person('Manuel "BatManuel" Reynolds', 'BatManuel', 'Batmanuel_cir.png', <<<'BIO'
Another year, another piece in the Gotham Times and the Daily Planet about BatManuel, Director of the Future! Still powered by organized chaos, dad jokes, and coffee, he is ready to let the fun times roll. He leveled up on Magic: The Gathering, dad jokes, D&D, and other crazy shenanigans. After all, he majored in crazy shenanigans at the University of Not Really a College. Although he is a little bored outside football season (Go Cowboys!), he is told there are other sports to keep him entertained. He does not see penguin curling listed on ESPN, so he guesses he'll do director work.
BIO
  ),
  pb_bio_person('Mariel "Bec" Folkerts', 'Bec', 'Bec_cir.png', <<<'BIO'
Bec is back for another amazing summer with PlanetBravo! First introduced to PlanetBravo as a camper in 2009, she has since risen through the ranks of CIT, intern, and counselor! Having graduated UC Santa Cruz with a BA in Art & Design for Games & Playable Media, she is now teaching during the school year too! She is excited to share her passion for digital art, modding, game design, and PlanetBravo spirit with the next generation of campers!
BIO
  ),
  pb_bio_person('Segil "Bender" Jack', 'Bender', '/img/staff-img/2025/bender-cir.png', <<<'BIO'
Bender Bending Rodriguez (otherwise known as Bending Unit 22, unit number 1,729, serial number 2716057) is a counselor for the second time this year! Born on a factory assembly line, Bender instead chose a life full of coding and digital paintball, attending camp as a camper and CIT for many years! Bender's favorite thing to do is obviously bending steel girders and beams, but when he's not, you can find him playing Digital Paintball 2, Minecraft, or Factorio! During the school year, Bender is studying Computer Science at UChicago. It's not bending, but it's pretty fun! Bend on!
BIO
  ),
  pb_bio_person('Henry "Big Bird" Cohen', 'Big Bird', 'BigBird_cir.png', <<<'BIO'
After long negotiations with Netflix and PBS, Big Bird is back at camp for his fourth summer as a counselor. Still the longest tenured member of Beverly Hills counting his long run as a camper, CIT, and Intern, Big Bird is excited to give it his all this summer. Outside of camp, he enjoys writing, video and board games, and hanging out with all of his Sesame Street pals.
BIO
  ),
  pb_bio_person('Tessa "Bingo" Lawton', 'Bingo', 'Bingo_cir.png', <<<'BIO'
Bingo is a past camper and CIT who recently graduated from California State University Northridge with a degree in film and television. On nights and weekends, you can find her playing games like Marvel Rivals and Tomodachi Life, enjoying board games, and making cosplay crafts. She is always looking for inspiration for her next film and loves to create in all forms of media. She is ready for this summer to be the best summer ever!
BIO
  ),
  pb_bio_person('Jessica "Bloom" Wilson', 'Bloom', 'Bloom_cir.png', <<<'BIO'
Bloom is ecstatic to be back for another year at PlanetBravo! This is her third year as a counselor, but she's had lots of time with the camp over the years, being a camper and CIT! In her free time, she likes reading, watching movies and TV (especially anything Star Wars related), and playing video games like Battlefront II, The Sims, and Subnautica 2! She can't wait for another amazing summer!
BIO
  ),
  pb_bio_person('Brandon "BMan" Hess', 'BMan', 'Bman_cir.png', <<<'BIO'
BMan is ready to rock camp and have a great time. After having been engulfed in all things PlanetBravo summer after summer, he's excited to be back to have a blast. After learning code and pursuing games for the future, BMan is ready to share his knowledge and help campers have the best time creating the best projects!
BIO
  ),
  pb_bio_person('David "Buttons!" Spencer', 'Buttons!', 'Buttons-cir.png', <<<'BIO'
Buttons! is back for summer number eleven! During the school year he teaches computer lab at Mar Vista, but when he's not teaching, he's playing board games, talking about movies, listening to ska music, or playing board games. He is VERY excited to introduce Board Game Camp this summer and can't wait for it to begin!
BIO
  ),
  pb_bio_person('Brody "Buggy" Eddy', 'Buggy', 'pb_logo.png', <<<'BIO'
Greetings, everybody! Buggy is super excited to be joining everyone this summer at PlanetBravo. When he gets a break from studying mechanical and aerospace engineering at Northeastern University, you can probably find him hiking, playing video games, sharing random geography and Star Wars facts, or playing soccer. To all his fellow Tottenham fans, the fans must stand together. He also loves 3D printing, rocketry, and trying to understand physics concepts that are far too advanced for him. He can't wait to make some fun and awesome memories this summer.
BIO
  ),
  pb_bio_person('Chris "Cheetoh" Donell', 'Cheetoh', 'Cheetoh_cir.png', <<<'BIO'
Cheetoh is joining us and excited for his first summer as a counselor! With a background in sports, working with kids, gaming, coding, and game design, he is ready to help campers have a great week at PlanetBravo. If he is not gaming, you can find him at the beach surfing or in the mountains skiing or mountain biking. He loves cars and especially driving them. He will be attending University of San Diego in the fall after graduating from Loyola High School.
BIO
  ),
  pb_bio_person('Robert "Chief" Campbell', 'Chief', 'Chief_cir.png', <<<'BIO'
Hello there from Chief of Manhattan Beach! This is his first summer as a counselor here at PlanetBravo! Chief started out as a camper back in 2017, and has gone all the way from CIT, to Intern, and now here he is. Chief enjoys playing video games (hence the name from the Halo games), watching TV/movies of any sort, Dungeons & Dragons, card games like Magic: The Gathering, and sports (including dodgeball). Chief has lived in SoCal his whole life, and is going to be attending Cal Poly SLO for Computer Engineering this next school year. He cannot wait to have a great time this summer, and never forget that the One Piece is real!
BIO
  ),
  pb_bio_person('Hailey "Chip" Smith', 'Chip', 'Chip_Cir.png', <<<'BIO'
Hey y'all! It's Chip (she/they)! This will most likely be their 5th or 6th summer at PB! Chip was a CIT in 2023 and an Intern in 2024, and now they are really excited to be a counselor this year! They are currently a Cinema Studies major and Game Studies minor at the University of Oregon in Eugene. In their free time, they like to kick back and watch some YouTube or a movie or play video games (who doesn't, right?). They also love to sing and play piano and are currently trying to teach themself guitar! Chip is looking forward to meeting y'all and can't wait to have an amazing summer at PB!
BIO
  ),
  pb_bio_person('Brooke "Cookie" Campbell', 'Cookie', 'Cookie_Cir.png', <<<'BIO'
Cookie is a biology major at UC Santa Barbara, former PlanetBravo camper, and now first-year camp counselor. When she's not in the lab, she's usually making jewelry, shredding the slopes on her snowboard, or absolutely locked in on Break A Lucky Block: Brainrot. She's bringing high energy, terrible jokes, and a very serious commitment to making this the most fun summer ever.
BIO
  ),
  pb_bio_person('Charles "Cowboy" Lyons', 'Cowboy', 'Cowboy_cir.png', <<<'BIO'
Howdy! The Cowboy is back riding 'round these parts after a year away. Now in his third summer at the Burbank camp, he's teaching Minecraft Modding! It's gonna be a wild ride, so get ready to explore the digital frontier with him!  During the year you can find Cowboy teaching tech at a public school in Glendale! Yee Haaw!
BIO
  , false),
  pb_bio_person('Kelli "Dr. English" Lycke', 'Dr. English', 'DrEnglish_cir.png', <<<'BIO'
Dr. English returns for a second year at PlanetBravo. The Doc loves dancing, sewing, playing board games, and watching obscure movies from the 1970s. She's looking forward to training her dodgeball throwing arm. She has 13 years of teaching experience and a doctor's degree in (you guessed it) English from the University of Arizona.
BIO
  ),
  pb_bio_person('Sydney "Envy" Foster', 'Envy', 'Envy_cir.png', <<<'BIO'
After a year of side quests (starting college), Envy has finally returned to try her hand at taking on the main quest of spreading joy and becoming a counselor at PlanetBravo! Buckle up, it's going to be a good summer! She has beaten every level from camper to Intern and is incredibly excited to be teaching. Envy loves to spend her time singing along to the radio, movie musicals, musicals (okay, she just might like singing), and dancing (she may have been a theater kid in high school). Along with that, she also loves playing the occasional video game, with some of her favorites being Breath of the Wild, Stardew Valley, and Dave the Diver. Envy has been training all year to be a strong ally or enemy on the dodgeball field this summer. Watch out for Envy!
BIO
  ),
  pb_bio_person('Sam "Falcon" Wilson', 'Falcon', 'Falcon_cir.png', <<<'BIO'
This is Falcon's fourth year as a counselor at PlanetBravo, but their history with PlanetBravo goes way back to being a camper and CIT! Falcon is thrilled to be returning again this year as a counselor. During the year, Falcon is pursuing technical theatre and sociology and has a love for various creative activities like drawing and writing. They also love to read, play video games, and watch shows. They spend a lot of time building sets for plays and musicals. Falcon is very excited to be back and they're ready to have another fun and exciting summer with the campers!
BIO
  ),
  pb_bio_person('Daniel "Goldfish" Nalick', 'Goldfish', 'Goldfish_cir.png', <<<'BIO'
Goldfish is back again for a third summer as a counselor after having been both a camper and CIT when he was younger. While he spends most of his year away in Illinois studying Computer Science as an incoming senior at Bradley University, he grew up in Pasadena, CA. He loves video games, cooking and eating food, ultimate frisbee, volleyball, writing, and reading. The games that Goldfish enjoys include too many to name, but some include Pokemon, Super Smash Bros., Overwatch, The Legend of Zelda, and many more. This Goldfish will try to smile back at you, but be careful during dodgeball because this fish has arms!
BIO
  ),
  pb_bio_person('Bella "Guppy" Lee', 'Guppy', 'guppy_cir.png', <<<'BIO'
Emerging from her fishbowl castle, Guppy is excited for another year at PlanetBravo! She loves swimming (obviously), scuba diving, drawing, and solving puzzles. She's always ready to play any game under the sun, so don't be shy to challenge her (and if you don't know any games, don't worry, she'll teach you!). During the school year when she's not at PlanetBravo, Guppy studies neurobiology at UC Davis where she learns about learning!
BIO
  ),
  pb_bio_person('Heismay', 'Heismay', 'Heismay_cir.png', <<<'BIO'
One of the few brand-new instructors in YEARS at our Encino location, Heismay grew up around these parts, but spends most of his time in Amherst, MA at Hampshire College finishing up his degree. Outside of school, Heismay is a video game developer, a music creator and editor, as well as a singer! He cannot wait to bring some of the joy of being creative to his great campers! 
BIO
  , false),
  pb_bio_person('Jasper "Hideo" Ferehawk', 'Hideo', '/img/staff-img/2021/Hideo_Jasper_2019.png', <<<'BIO'
Hideo is back at PlanetBravo for his fourth summer! He's taken a few years off between his last summer, but Hideo is a techie and nerd at heart and couldn't resist another summer of PlanetBravo fun. Hideo is a graduate of Beloit College   with a deep love for anime, manga, and all things Japanese. He spends his non-camp time in classrooms serving as credentialed substitute. Besides his hobbies, he loves solving problems and helping others out!
BIO
  , false),
  pb_bio_person('Olivia "io" Lydick', 'io', 'io-cir.png', <<<'BIO'
IO hails from our Irvine location and has been a staple of PlanetBravo for years! This week, she is stepping back in and helping Mr. Dude close out the week at Westchester as camp director. When not working, you can find her listening to music, drawing, or hula hooping! 
BIO
  , false),
  pb_bio_person('Fletcher "J.A.R.V.I.S." Boydston', 'J.A.R.V.I.S.', 'JARVIS_cir.png', <<<'BIO'
Returning to PB as a counselor for his fifth summer, J.A.R.V.I.S. started out as a camper back in 2013 and has loved coming back every year. In his spare time, J.A.R.V.I.S. loves reading, playing video games, sports, and making up stories. He's got a particular affinity for anything science fiction, but does always enjoy a good magical fantasy setting.
BIO
  ),
  pb_bio_person('Hali "Kirby" Castro', 'Kirby', 'Kirby_cir.png', <<<'BIO'
Kirby is back for another crazy summer at Manhattan Beach! But this time, she's in boss mode as the STEM Director! During the year she teaches tech but is always looking forward to the special magic of summer creation and summer fun, like dodgeball! When she's not watching over the little Waddle Dees and Doos, she's lounging with her two dogs, playing Minecraft, and generally chillin' with a coffee or boba, or both. She's excited to see you soon!
BIO
  ),
  pb_bio_person('Keira "Kit Kat" Lavelle', 'Kit Kat', 'KitKat_cir.png', <<<'BIO'
Kit Kat is excited for another year at PlanetBravo! Kit Kat likes playing Minecraft, lifting, playing racket sports, going to the beach, and reading. She is also obsessed with matcha. During the school year when she's not at PlanetBravo, she studies AI and Web Development at Purdue University.
BIO
  ),
  pb_bio_person('Luca "L00na" Montague', 'L00na', 'L00na_cir.png', <<<'BIO'
It's dangerous to go alone... take L00na! After a snowed-in spring in the northeast, he's excited to be back for his second summer as a counselor! When he isn't spreading knowledge and fun throughout the land, L00na can be found playing a bit too much Marvel Rivals, reading comics, running his D&D campaign, and spending the school year majoring in media production at Emerson College. He's ready for an awesome, and hopefully sunny, summer with PlanetBravo. Don't let the zeroes fool you, he's always ready to give 100%, and you've only seen a fraction of his power!
BIO
  ),
  pb_bio_person('Nicole "Lemon" Galins', 'Lemon', 'Lemon_cir.png', <<<'BIO'
Lemon is back again, except this time, she's finally a full-fledged counselor at PlanetBravo's Manhattan Beach camp! Having been a camper herself since 2014, Lemon brings a wealth of experience and enthusiasm to her second year as a counselor. With seven years of experience in Clickteam Fusion and eleven years in Minecraft, Lemon's tech skills are top-notch. She also has a background in architecture and social media. Her extensive experience as a CIT and Intern at PlanetBravo and time as a Girl Scout has equipped her with the skills to support and inspire our campers. Lemon's love for PlanetBravo and its vibrant community is contagious, making her an invaluable part of our team! When she is not here at camp, she's attending the University of Arizona studying Architecture! Lemon can't wait to have another fun summer with everyone!
BIO
  ),
  pb_bio_person('Liza "Lizard" Unger', 'Lizard', 'Lizard_cir.png', <<<'BIO'
Lizard is so happy to be here for her first summer at PlanetBravo! She moved here from NorCal to pursue Game Design in Concept Art, so it's very exciting for her to be helping out in a field that she enjoys herself. Besides art, she enjoys cooking, driving with music and friends, and playing video games such as Roblox, Tomodachi Life, and Spider-Man. She's looking forward to an amazing summer here!
BIO
  ),
  pb_bio_person('Noelle "Lucky" Reicherts', 'Lucky', 'Lucky_cir.png', <<<'BIO'
This is Lucky's 3rd year on PlanetBravo and she is over the moon with excitement for another terrific summer at PB Burbank. During the school year, Lucky enjoys life as a singer, educational performer, and substitute teacher. Known to many as the "Human Jukebox," Lucky is an LA native who can often be found frolicking in the sun among the clovers or dancing and singing under the moonlight with friends. She is a creative and lively songwriter who enjoys traveling, socializing, outdoor activities (especially involving water), board games, theme parks, cosplay, karaoke, spending time with friends/family, and nights in with her beloved kitty, Bailey.
BIO
  ),
  pb_bio_person('Ivy "Mako" Guerrero', 'Mako', 'mako_cir.png', <<<'BIO'
Mako is returning this year for her third year at PlanetBravo. She is so excited to see returning campers and make some new friends as well! She works at an elementary school with kids from TK to 8th grade. Outside of camp, Mako likes to go to the beach, walk her dogs, listen to music, and go to theme parks. She would love to hear your ocean fun facts if you see her!
BIO
  ),
  pb_bio_person('Xander "Mango" Hadley', 'Mango', 'Mango_cir.png', <<<'BIO'
This is Mango's second year as a counselor! Mango has been a CIT for a couple summers, and a camper since the first grade. Mango is studying Illustration Design at the ArtCenter College of Design, and loves to draw, paint, sculpt, and all things art! After classes, Mango loves training/watching combat sports and playing video games like Deadlock, Balatro, and Smash Brothers. Mango is excited to work with campers to bring out their creative side and create some awesome work!
BIO
  ),
  pb_bio_person('Lucas "Mario" Ancewicz', 'Mario', 'Mario_cir.png', <<<'BIO'
Howdy, y'all! Mario lives in San Fernando and is excited to meet everyone this summer at PlanetBravo. Growing up, he went to his fair share of summer camps and is excited to bring that same excitement to all of our campers! He is currently studying mechanical engineering at UCR, finishing his second year and loving every step of his journey. He loves everything Star Wars and is a huge drone technology geek. In his free time, he flies his FPV drone and works to minimize his crashing. He can't wait to meet everyone this summer and experience it as a team.
BIO
  ),
  pb_bio_person('Maddie "Mermel" Mermelstein', 'Mermel', 'pb_logo.png', <<<'BIO'
Previously a camper and a CIT, Mermel is excited for their first summer as a PlanetBravo counselor! They're practically a vampire at this point, as between coding and theatre, Mermel certainly hasn't stepped outside in weeks. As a certified nerd, after camp, they're off to MIT to study computer engineering where they will continue to avoid touching grass by any means necessary. Before then, though, they can't wait to see what this summer has in store!
BIO
  ),
  pb_bio_person('Morgan "Morgana" Crosby', 'Morgana', 'Morgana_cir.png', <<<'BIO'
After 11 years of honing her magic at PlanetBravo, Morgana is back to OVERTHROW, uh, spread knowledge to all of the lovely campers! When not plotting to curse King Arthur or devising other evil schemes, Morgana can be found crafting, making music, building rockets, or studying astronautical engineering at USC. She cannot wait for another action-packed year of camp!
BIO
  ),
  pb_bio_person('Glenn "Mr. Dude" Rodriguez', 'Mr. Dude', 'MrDude-cir.png', <<<'BIO'
The Dude has returned to direct the best camp ever with the best campers ever. He hopes you are just as excited as he is to have the best summer at PlanetBravo camp.
BIO
  ),
  pb_bio_person('Samuel "Mr. Giggles" Behar', 'Mr. Giggles', 'MrGiggles_cir.png', <<<'BIO'
Mr. Giggles is known for his extreme no-nonsense attitude and his rare and intense commitment to fun, silliness, and whimsy. He's from LA and currently studies Electrical Engineering at UC San Diego, but when he's not reconsidering his academic choices, he DJs, plays Valorant/Overwatch/Roblox, and overcomplicates simple tasks with Arduinos. This will be his first year at PB, but he's looking forward to a fantastically awesome summer. Let it be known that anything impeding the pursuit of fun and joy will be met by the full, unadulterated wrath of... Mr. Giggles.
BIO
  ),
  pb_bio_person('Christian "Mr. Stitch" Mulrooney', 'Mr. Stitch', 'MrStitch_cir.png', <<<'BIO'
Hi friends! Mr. Stitch (a big fan of Lilo & Stitch, of course!) is so excited to join PlanetBravo for the second time! He grew up in California's Central Valley, close to Sequoia and Yosemite National Parks, so living in the city has been a brand-new adventure for him. In his free time, he loves spending time in nature, reading fun books, and shopping for cool vinyl records. He also really enjoys photography, especially taking pictures of the moon! Mr. Stitch is currently a graduate student studying school psychology at Cal State Los Angeles and will finish in one year. He can't wait for all the fun adventures at PlanetBravo and is so excited to see everyone!
BIO
  ),
  pb_bio_person('Abra "Neptune" Ramirez', 'Neptune', 'pb_logo.png', <<<'BIO'
Neptune is joining camp at PlanetBravo for her first year and is super excited to meet everyone! She is originally from Sacramento. As a visual artist, she loves illustrating, video editing, and futuristic tech things like sci-fi movies and robots. Because of her love of technology, she went back to school last year and received her certification in Robotics Programming earlier this year. She's looking forward to continuing to be around cool tech, being a part of this wonderful experience at PlanetBravo, and hopefully making new friends!
BIO
  ),
  pb_bio_person('Oyinkansola "Olasores" Olayinka', 'Olasores', 'Olasores_cir.png', <<<'BIO'
Ola is back at PlanetBravo for her third summer! She's always up for a side quest involving good music or books, but mostly she's just excited to spend the summer with the greatest people ever.
BIO
  ),
  pb_bio_person('OfTroy', 'OfTroy', 'OfTroy_cir.png', <<<'BIO'
OfTroy has been involved with Planet Bravo for the past 10 years as a camper, CIT, Intern, and now is entering their first summer as a counselor. She will be studying Engineering at the University of Washington in the fall. When not trapped in her room studying, OfTroy can be found baking, sewing, and destroying her family at board games. They also have a love for theater, 70s music, and kayaking. She looks forward to a great summer at PlanetBravo, and is excited to be making an unexpected return!
BIO
  ),
  pb_bio_person('Eli "Oz" Alper', 'Oz', 'Oz_cir.png', <<<'BIO'
Returning for his 10th year at PlanetBravo, Oz returns once again for another summer of wacky adventures and fun times! As a rising junior and sociology major at Bard College (with a concentration in politics), Oz spends most of his time outside camp locked in to learning about the way the world works and the way societies are formed and function, but outside of school Oz spends lots of time in the outdoors, hiking, swimming, and camping. He also has a special place in his heart for music, having played the bass for around two years and also dabbling in guitar.
BIO
  ),
  pb_bio_person('Jackie "P03" Andrade', 'P03', 'p03_cir.png', <<<'BIO'
Previously a camper, CIT, and Intern, and traversing across a whopping THREE PlanetBravo locations, P03, the multiverse's favorite Scrybe of Technology, is finally here for their very first year as a counselor! Oddly enough, they can be found studying English at UCLA beginning in the fall. Known for having multiple decks of cards on them at all times, being part of classic PB 1995 hit band, The Anagrams (Master Pecc, Campers Etc.), and thinking a little bit too hard about absolutely everything ever, they're excited to return to PB for another summer of making lore-heavy big-shot movies and next-level games. Their love of film, editing, TTRPGs, theatre, and music will hopefully contribute to a lively summer at Board Game Camp. Not coming back would've been a total misplay.
BIO
  ),
  pb_bio_person('Nicholas "Pez" Pirotto', 'Pez', 'Pez_Cir.png', <<<'BIO'
This will be Pez's seventh official summer as a counselor, and their record for having fun at PlanetBravo goes way back to being a camper, CIT, and intern! They love to play video games and read gigantic books in their spare time in order to conserve their energy, which they can unleash to become a Super Counselor, capable of fixing any code errors! When not building houses in Minecraft, they work as a tutor at schools around Los Angeles! Pez is excited to have a fantastic summer and see all the old and new faces!
BIO
  ),
  pb_bio_person('Paige "Pip" Carlovsky', 'Pip', 'Pip_cir.png', <<<'BIO'
Hello everyone! This is Pip's THIRD year with PB! She's so excited for all the campers, counselors, CITs, and everyone involved to make this summer a great one. Pip is originally from a small town in Illinois and came to Los Angeles for a degree in Dance Theatre. Dance led Pip into yoga where she taught all around Los Angeles for about 7 years! As Pip began to teach other people how to teach yoga, she fell in love with the art of teaching. Nowadays, Pip is an elementary school teacher and could not be happier. When she's not teaching or doing yoga, Pip enjoys her time reading, knitting, and playing with her two little chihuahuas. She cannot wait for her third year with PlanetBravo and knows it's going to be a fabulous summer!
BIO
  ),
  pb_bio_person('Natalie "Pixel" Baber', 'Pixel', 'pixel-cir.png', <<<'BIO'
Pixel first came to this world somewhere around the turn of the century, only a few short miles from PlanetBravo Encino's future location. After trying all manner of treacherous art and science summer camps looking for a place to call home, the search ended with PlanetBravo. After graduating from CIT to full-blown camp counselor, Pixel graduated one more time, from California Institute of the Arts, and continues to merge animation and imagination with cyberspace, sharing skills with young grasshoppers along the way. This will be Pixel's ninth summer as a PB counselor, and she is thrilled to discover the true meaning of unc status.
BIO
  ),
  pb_bio_person('Cayenne "Pokeee" Lee', 'Pokeee', 'Pokeee_cir.png', <<<'BIO'
Howzit!! Pokeee is excited to be back at PlanetBravo this summer!! Born in Hawaii, but raised in Los Angeles, she started off as a little camper, worked as a CIT and Intern, and is ready for her 3rd year as a camp counselor! Pokeee is currently attending Loyola Marymount University majoring in psychology with a double minor in Disability Studies and Asian and Pacific Studies. She enjoys cooking, eating food, playing tennis, traveling, coloring, and playing video games such as Overcooked, Paladins, Wii Sports, and Just Dance. Pokeee is super stoked to be back as a counselor!! Look out for her, she's coming in hot in dodgeball!! CHEEHOO!!
BIO
  ),
  pb_bio_person('Christopher "Psyc" Wantuch', 'Psyc', 'Psyc_cir.png', <<<'BIO'
They just can't contain the FUN here at Techno-Tainment camp. Psyc is back at it again working with his favorite camp as Director of FUN! Teaching kids awesome tech for 9 summers just wasn't enough. When he's not participating in the fun, you can find him editing videos, helping people with their tech, teaching year-round, playing LOTS of video games, and watching movies and anime. Born and raised in SoCal, he can't wait to soak up some sun in Irvine this summer! See you cats sunny side up. PSYC, I like my eggs scrambled.
BIO
  ),
  pb_bio_person('Shiloh "Rainbow" Hart', 'Rainbow', 'Rainbow_cir.png', <<<'BIO'
Rainbow is excited for their first summer with PlanetBravo! With a background in art, design, and education, Rainbow loves helping kids learn through creativity, curiosity, and hands-on exploration. When not at camp, you can usually find them making art, searching for great live music, hanging out with their rescue dog Twig, or working on creative projects around Los Angeles. Originally from Portland, Oregon, Rainbow recently returned to LA and is looking forward to a summer full of STEM adventures, imagination, and fun.
BIO
  ),
  pb_bio_person('Dylan "Slime" Crowley-Loo', 'Slime', 'Slime_cir.png', <<<'BIO'
Hey there! It's Slime, and he's really excited to be back at PlanetBravo for a seventh summer! When he's not at camp, Slime is a Master's of Public Health student at USC studying how to keep people healthy and living their best lives. For fun, he does a bit of everything: plays trumpet in the USC marching band, enjoys 3D animation, makes video games, builds LEGOs, and collects board games. Odds are, he's probably at a street food stand eating takoyaki. Slime is super excited to see you soon at camp!
BIO
  ),
  pb_bio_person('Cameron "Spamz" Lee', 'Spamz', 'Spamz_cir.png', <<<'BIO'
Aloha! Spamz is ready to make his return to PlanetBravo as a 2nd year counselor. Originating from the island of Oahu, Hawaii, Spamz is excited to be back. He has been in Southern California for some time, starting his time at PlanetBravo as a little camper and CIT. Spamz currently attends the University of Southern California, studying at the Marshall School of Business (Business Administration and Finance) and School of Cinematic Arts (Game Entrepreneurship). He enjoys "attempting to cook," eating food, playing sports (volleyball, basketball), paddling, and video games (League of Legends, Valorant).
BIO
  ),
  pb_bio_person('Alex "Spirit" Manibog', 'Spirit', 'Spirit_cir.png', <<<'BIO'
Spirit is back again after taking a year away! Once again, he's at the Junior STEM Camp, right where he was meant to be all along! Spirit has been with PlanetBravo Pasadena since 2013, and is a counselor for his fourth year. When not at PlanetBravo, Spirit enjoys playing many instruments like guitar, saxophone, and piano, and playing many games like Overwatch, Valorant, and Minecraft. Spirit is a STEM major in college, and is so excited to have fun with STEM projects, play, and meet everyone this summer.
BIO
  , false),
  pb_bio_person('Adam "Spore" Goren', 'Spore', 'Spore_cir.png', <<<'BIO'
Spore has been a part of PlanetBravo since he was 8(!) years old. He has since spent 6 summers as a camper, 2 as a CIT, 1 as an intern, and 9 as a counselor. When he's not at camp or teaching tech at school, Spore loves making music, editing videos, writing stories, and playing video games. He can't wait to have another awesome summer with PB, full of fun, learning, and kicking butt at dodgeball!
BIO
  ),
  pb_bio_person('Jeremy "Spot" Leopard', 'Spot', 'Spot_cir.png', <<<'BIO'
Having spent 7 summers at PlanetBravo as either a camper, CIT, or intern, Spot is super excited for his first summer as a counselor! Spot enjoys spending his free time playing video games, hanging out with friends, composing music, and making video games. In the fall, Spot will be attending UC Santa Cruz, majoring in Computer Science: Computer Game Design!
BIO
  ),
  pb_bio_person('Mason "Star" O\'Brien', 'Star', 'Star2_cir.png', <<<'BIO'
This is Star's second year as a counselor! Star was a camper multiple times in the past dating back to 2014, before he was even a star. But now he is only starting to shine brighter as he studies Computer Game Design at UC Santa Cruz. Star LOVES creativity and uniqueness because after all, no star sparkles the same! His favorite games are Sekiro and Noita, but he has recently been playing lots of Trackmania and Subnautica 2. Talk to Star about any shows/animes, movies, books, games, or even music and he will certainly listen, because he always listens to stellar frequencies. Shine bright!
BIO
  ),
  pb_bio_person('Constance "Storm" Chun', 'Storm', 'Storm_cir.png', <<<'BIO'
Storm works with kids aged 4-18 during the summer and school year. Her interests include playing volleyball, watching and discussing films, and Minecraft. The name Storm comes from her favorite comic book hero, Storm from X-Men. Now she attends University College Dublin for a Bachelor of Science. Storm has been to many sports, STEM, and summer camps growing up, and can't wait to be part of a summer experience full of fun and learning that leads to bright futures!
BIO
  ),
  pb_bio_person('Jacob "Switch" Berman', 'Switch', 'Switch_cir.png', <<<'BIO'
Switch is back for his second year as a counselor at PlanetBravo. After being born from his cardboard box, he's spent years working on powering and playing all your favorite games both at home and on the go. When he's not being used to help humans explore Hyrule, the Mushroom Kingdom, and approximately 12,500 other game worlds, you may find him at college writing about and making movies and video games in the frozen land of Massachusetts. Or perhaps you'll catch him spending time at the movie theaters or watching the Dodgers in Los Angeles. And last but not least, he may be up way too late playing Nintendo games or indie games on his own Switch and computer.
BIO
  ),
  pb_bio_person('Karishma "Table" Dhawan', 'Table', 'Table_cir.png', <<<'BIO'
Table's name comes from back when she was a camper in 2018, surviving her CIT and Intern years as well. Table can be found at UCSD in the fall studying Biology. When not at camp, Table loves playing in orchestra (viola), watching movies, and playing hacky sack! She's so, so, so pumped for another amazing, perfect, and best summer!
BIO
  ),
  pb_bio_person('John "Ted the Bread" McManus', 'Ted the Bread', 'TedTheBread_cir.png', <<<'BIO'
Ted first joined PlanetBravo in 2016 before he transcended to bread. During his CIT/Intern years, Ted was tasked with assisting difficult classes with overly complicated software (Unity). Throughout the years, he gained skills to help design and program games, and he is ready to be the bread of the campers' creativity sandwich as he once was when he was one of them. As a hobby, Ted composes D&D-style music, which is a possible future for him, but as for now his options are still open. Let 2026 be the year of Ted Two Bread.
BIO
  ),
  pb_bio_person('Jack "Vital" Doody', 'Vital', 'vital-cir.png', <<<'BIO'
Hailing from the grand planet of Coruscant, Vital is working as a second-year counselor at PlanetBravo. When he's not spending his days fueling the rebellion against the Galactic Empire, you can find him avidly discussing Star Wars or playing Deadlock. During the school year, he studies Computer Science in the redwood forests of Endor, also known as UC Santa Cruz.
BIO
  ),
  pb_bio_person('Stephanie "Zeta" Ressler', 'Zeta', 'Zeta_cir.png', <<<'BIO'
An advanced synthoid powered by caffeine and musical numbers, Zeta is excited to utilize their experience as a professional animator and hobbyist techy to serve the good denizens of PlanetBravo! In between gaming sessions and late night draw-a-thons, Zeta enjoys watching cartoons, doting on their cats, and practicing everyday ninjutsu. KA-POW!
BIO
  ),
  pb_bio_person('Zilla', 'Zilla', 'Zilla_cir.png', <<<'BIO'
For one week only, former camper and CIT extraordinaire Zilla makes his debut as our Python coding instructor at the Eagle Rock location. Zilla made his name at PlanetBravo during the first pandemic spring break camp. Online, Zilla stood out for his talents on and off the computer. Not only gifted with technology, Zilla is a creative guy, issuing year after year customized MTG cards for all staff.
BIO
  )
);

usort($staff, function($a, $b) use ($pb_bio_staff_token) {
  $a_key = pb_bio_normalize_name($a['camp']);
  $b_key = pb_bio_normalize_name($b['camp']);
  $a_location = !empty($pb_bio_staff_token['loc'][$a_key]) ? $pb_bio_staff_token['loc'][$a_key] : 0;
  $b_location = !empty($pb_bio_staff_token['loc'][$b_key]) ? $pb_bio_staff_token['loc'][$b_key] : 0;
  $a_highlight = !empty($pb_bio_staff_token['hi'][$a_key]) ? $pb_bio_staff_token['hi'][$a_key] : 0;
  $b_highlight = !empty($pb_bio_staff_token['hi'][$b_key]) ? $pb_bio_staff_token['hi'][$b_key] : 0;
  $a_director = !empty($pb_bio_staff_token['dir'][$a_key]) ? $pb_bio_staff_token['dir'][$a_key] : 0;
  $b_director = !empty($pb_bio_staff_token['dir'][$b_key]) ? $pb_bio_staff_token['dir'][$b_key] : 0;

  if ($a_highlight && !$b_highlight) return -1;
  if (!$a_highlight && $b_highlight) return 1;
  if ($a_highlight && $b_highlight && $a_highlight != $b_highlight) return $a_highlight < $b_highlight ? -1 : 1;
  if ($a_director && !$b_director) return -1;
  if (!$a_director && $b_director) return 1;
  if ($a_director && $b_director && $a_director != $b_director) return $a_director < $b_director ? -1 : 1;
  if ($a_location && !$b_location) return -1;
  if (!$a_location && $b_location) return 1;
  if ($a_location && $b_location) return strcasecmp($a['camp'], $b['camp']);
  return strcasecmp($a['camp'], $b['camp']);
});

$pb_bio_location_staff = array();
$pb_bio_rest_staff = array();
foreach ($staff as $person) {
  $person_key = pb_bio_normalize_name($person['camp']);
  $is_location_team = !empty($pb_bio_staff_token['hi'][$person_key]) || !empty($pb_bio_staff_token['dir'][$person_key]) || !empty($pb_bio_staff_token['loc'][$person_key]);
  if ($pb_bio_has_staff_token && $is_location_team) {
    $pb_bio_location_staff[] = $person;
  } else {
    $pb_bio_rest_staff[] = $person;
  }
}
$pb_bio_display_staff = $pb_bio_has_staff_token ? array_merge($pb_bio_location_staff, $pb_bio_rest_staff) : $staff;
?>

<div class="show-for-large-up">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/subnav-camps-top.php'; ?>
</div>
<div class="hero text-center">
  <div class="row">
    <div class="small-12 large-8 large-centered columns">
      <h1>Meet the Staff</h1>
      <h2>PlanetBravo STEM Camps</h2>
      <div class="small-12 medium-11 large-8 medium-centered text-center columns"> 
        <!-- <a href="/news-update.php" class="button secondary-button wow fadeIn" data-wow-delay=".4s" style="background-color: #ff5000">Info regarding COVID-19</a> --></div>
    </div>
  </div>
</div>
<div class="staff-section">
  <div class="row">
    <?php if ($pb_bio_has_staff_token) { ?>
      <div class="pb-bio-section-heading">
        <h2>The <?php echo htmlspecialchars(!empty($_GET['pb_location']) ? $_GET['pb_location'] : 'Camp', ENT_QUOTES, 'UTF-8'); ?> Team</h2>
      </div>
      <ul class="small-block-grid-2 medium-block-grid-5 course-grid">
      <?php foreach ($pb_bio_location_staff as $person) {
        $index = array_search($person, $pb_bio_display_staff, true);
        $modal_id = 'course-info' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
        $asterisk = !empty($person['needs_update']) ? '*' : '';
        $person_key = pb_bio_normalize_name($person['camp']);
        $is_location_team = !empty($pb_bio_staff_token['hi'][$person_key]) || !empty($pb_bio_staff_token['dir'][$person_key]) || !empty($pb_bio_staff_token['loc'][$person_key]);
        $focus_class = !empty($pb_bio_staff_token['hi'][$person_key]) ? 'pb-bio-focused' : ($is_location_team ? 'pb-bio-location' : '');
        $director_label = !empty($pb_bio_staff_token['dir'][$person_key]) ? ' (Director)' : '';
      ?>
        <li class="<?php echo htmlspecialchars($focus_class, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="">
            <a href="#" data-reveal-id="<?php echo $modal_id; ?>" title="View course details"><img src="<?php echo htmlspecialchars(pb_bio_image_src($person['image']), ENT_QUOTES, 'UTF-8'); ?>" alt="PlanetBravo All-Star" class="wow bounceIn"></a>
          </div>
          <dt class="text-center"><?php echo htmlspecialchars($person['camp'] . $director_label . $asterisk, ENT_QUOTES, 'UTF-8'); ?></dt>
        </li>
      <?php } ?>
      </ul>
      <div class="pb-bio-section-heading pb-bio-rest-heading">
        <h2>The Rest of Our Team</h2>
      </div>
      <ul class="small-block-grid-2 medium-block-grid-5 course-grid">
      <?php foreach ($pb_bio_rest_staff as $person) {
        $index = array_search($person, $pb_bio_display_staff, true);
        $modal_id = 'course-info' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
        $asterisk = !empty($person['needs_update']) ? '*' : '';
      ?>
      <li>
        <div class="">
          <a href="#" data-reveal-id="<?php echo $modal_id; ?>" title="View course details"><img src="<?php echo htmlspecialchars(pb_bio_image_src($person['image']), ENT_QUOTES, 'UTF-8'); ?>" alt="PlanetBravo All-Star" class="wow bounceIn"></a>
        </div>
        <dt class="text-center"><?php echo htmlspecialchars($person['camp'] . $asterisk, ENT_QUOTES, 'UTF-8'); ?></dt>
      </li>
      <?php } ?>
      </ul>
    <?php } else { ?>
      <ul class="small-block-grid-2 medium-block-grid-5 course-grid">
      <?php foreach ($staff as $index => $person) {
        $modal_id = 'course-info' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
        $asterisk = !empty($person['needs_update']) ? '*' : '';
      ?>
        <li>
          <div class="">
            <a href="#" data-reveal-id="<?php echo $modal_id; ?>" title="View course details"><img src="<?php echo htmlspecialchars(pb_bio_image_src($person['image']), ENT_QUOTES, 'UTF-8'); ?>" alt="PlanetBravo All-Star" class="wow bounceIn"></a>
          </div>
          <dt class="text-center"><?php echo htmlspecialchars($person['camp'] . $asterisk, ENT_QUOTES, 'UTF-8'); ?></dt>
        </li>
      <?php } ?>
      </ul>
    <?php } ?>
  </div>
</div>
<hr>
<hr>
<div class="staff-section">
  <?php foreach ($pb_bio_display_staff as $index => $person) {
    $modal_id = 'course-info' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
    $asterisk = !empty($person['needs_update']) ? '' : '';
    $person_key = pb_bio_normalize_name($person['camp']);
    $director_label = ($pb_bio_has_staff_token && !empty($pb_bio_staff_token['dir'][$person_key])) ? ' (Director)' : '';
  ?>
  <div id="<?php echo $modal_id; ?>" class="reveal-modal course-info" data-reveal>
    <h3><?php echo htmlspecialchars($person['name'] . $director_label . $asterisk, ENT_QUOTES, 'UTF-8'); ?></h3>
    <div class="description">
      <img height="240" width="240" src="<?php echo htmlspecialchars(pb_bio_image_src($person['image']), ENT_QUOTES, 'UTF-8'); ?>" style="margin-bottom: 10px; margin-right: 10px;">
      <p class="clear-on-small"><?php echo nl2br(htmlspecialchars($person['bio'], ENT_QUOTES, 'UTF-8')); ?><br>
      <a class="close-reveal-modal">&#215;</a>
    </div>
  </div>
  <?php } ?>
</div>
<div class="row">
  <div class="small-12 hide-for-large-up columns">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/subnav-camps.php'; ?>
  </div>
</div>
<!-- END BODY CONTENT -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<!-- BEGIN CUSTOM SCRIPTS --> 
<script async src="/js/faq.js"></script> 
<!-- END CUSTOM SCRIPTS -->
</body></html>
