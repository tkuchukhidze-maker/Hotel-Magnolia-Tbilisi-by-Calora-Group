# Keyword & question research — Hotel Magnolia Tbilisi

**სტატუსი:** provisional (თვისებრივი კვლევა; მოთხოვნის/მოცულობის მონაცემები ხელმისაწვდომი არ იყო)
**თარიღი:** 2026-10-27 · **საიტი:** https://magnoliatbilisi.com · **მფლობელი:** Calora Group / საიტის გუნდი
**შემდეგი ნაბიჯი:** [keyword-map.md](keyword-map.md) → [content-gap.md](content-gap.md) → [keyword-decisions.md](keyword-decisions.md)

## 1. გადასაწყვეტი საკითხი

რომელ ძიებებსა და კითხვებზე უნდა „ფლობდეს“ პასუხს თითოეული გვერდი (5 არსებული + დაგეგმილი), რომ Magnolia-მ მიიღოს **პირდაპირი ჯავშნები** Google-იდან და AI პასუხებიდან (ChatGPT, Gemini, Perplexity, Google AI Overviews), OTA-ებზე დამოკიდებულების ნაცვლად.

- **ბაზარი/ენა:** en (საერთაშორისო ტურისტები) — მთავარი; ru (რუსულენოვანი მოგზაურები) — მეორე; ka (ადგილობრივი) — მესამე. ⚠️ ბაზრების წილი არ არის დადასტურებული პირველი მხარის მონაცემით — ეს ჰიპოთეზაა.
- **აუდიტორია:** წყვილები და მარტო მოგზაურები მოკლე ვიზიტზე; ოჯახები (3 ადამიანი); მანქანით მოგზაურები / road-trip; ფასზე მგრძნობიარე სტუმრები, რომლებიც OTA-ს და პირდაპირ ფასს ადარებენ.
- **ფარგლებს გარეთ:** რესტორანი/ღონისძიებები (საიტზე არ არის), გრძელვადიანი ქირა, Batumi/Kutaisi.

## 2. მტკიცებულების წყაროები და შეზღუდვები

| წყარო | რა მოგვცა | შეზღუდვა |
|---|---|---|
| საიტის 5 გვერდი + robots/sitemap/llms.txt ([raw](raw/site-capture-2026-10-27.md)) | ფაქტები, არსებული დაფარვა, placeholder-ები | WebFetch-ის შეჯამებული ექსტრაქცია; raw HTML ვერ შევინახე (shell-ს ქსელი დაბლოკილი აქვს), JSON-LD/hreflang/lang = „დაუდასტურებლად არ არის“ |
| 13 ძიების დაკვირვება ([raw](raw/serp-observations-2026-10-27.md)) | შედეგების ტიპი, კონკურენტები, intent | ზოგადი ვებ-ინდექსი (US), არა ლოკალიზებული Google SERP; რუკის/Hotel pack-ის/PAA-ს გარეშე; პოზიციების მტკიცება არ ხდება |
| OTA გვერდები (Expedia h79660622, Hotels.com ho2550139904) | ბრენდის ერთეული მესამე მხარეზე: Mtatsminda, 7.8/10, 36 მიმოხილვა | ერთჯერადი სნეპშოტი |
| Search Console / Bing WMT / Keyword Planner / Trends | — | **ხელმისაწვდომი არ იყო.** ყველა მოცულობა = `unavailable` |

**დასკვნა მეთოდზე:** ეს არის თვისებრივი shortlist + ფლობის რუკა. რაოდენობრივად (რომელი ტერმინია „ყველაზე დიდი“) კვლევა **არ არის დასრულებული** — იხ. [keyword-decisions.md § მოთხოვნის დამოკიდებულება](keyword-decisions.md#5-research-dependencies).

## 3. ძირითადი დაკვირვებები (ფაქტი)

1. **ბრენდულ ძიებაზე ოფიციალური საიტი არ ჩანს.** „Hotel Magnolia Tbilisi Kirile Ukleba“ და „Magnolia hotel Tbilisi“ — ყველა შედეგი OTA-ა (Expedia, Travelocity, Hotels.com, Lastminute). ბრენდის SERP-ს OTA-ები ფლობენ.
2. **რეიტინგის კონფლიქტი:** საიტი ამბობს „Booking.com 10, Exceptional“, OTA-ები აჩვენებენ **7.8/10 (36 მიმოხილვა)**. AI სისტემები და მომხმარებლები ორივეს ხედავენ.
3. **უბნის სახელი:** საიტი — „Old Town“; OTA-ები — **„Mtatsminda“**. Mtatsminda Pantheon 550 მ-ზეა. ⚠️ „Sololaki“-ს თუ „Mtatsminda“-ს გამოყენება მფლობელმა უნდა დაადასტუროს.
4. **სახელის შეჯახება:** „boutique hotel Old Tbilisi“ ძიებაში ჩანს კონკურენტი სასტუმრო სახელწოდებით „Boutique Old Tbilisi“ — ზოგადი ფრაზა ნაწილობრივ ბრენდულია.
5. **კომერციულ ძიებებს (near Freedom Square, parking, rooftop, family) ფლობენ OTA-ს სიები და ფილტრ-გვერდები**; ინფორმაციულს (where to stay, airport transfer) — ბლოგები.
6. **ქართული ძიება** სუსტ/შერეულ შედეგებს აძლევს (არქივი, Airbnb) — ნაკლები კონკურენცია, მაგრამ მოთხოვნა უცნობია.
7. **ენები:** საიტზე „EN · ქარ · RU“ მხოლოდ ტექსტია; /ka არ არსებობს; sitemap-ში hreflang არ არის.
8. **ძლიერი დიფერენციატორები, რომლებსაც OTA-ები ზოგადად აჩვენებენ, საიტი კი ცალკე არ ფლობს:** უფასო პარკინგი ძველ ქალაქში, სახურავის ტერასა მთაწმინდის ხედით, 127 წლის მაგნოლია, „pay at hotel“, Triple ოთახი ოჯახისთვის.

## 4. აუდიტორიის კითხვებისა და ძიებების კორპუსი

ID-ები ემთხვევა [`query-corpus.json`](query-corpus.json)-ს. ტიპი: **OQ** = დაკვირვებული ძიების ფორმა (ჩვენ შევამოწმეთ შედეგები), **UN** = მომხმარებლის საჭიროება/კითხვა (ჰიპოთეზა საიტის FAQ-დან, OTA ფილტრებიდან და ბლოგებიდან), **AI** = AI პრომპტის ჰიპოთეზა (არა დაკვირვებული).

### F01 · ბრენდი (navigational, decision/trust)
- OQ `q-brand-en` — hotel magnolia tbilisi (+ kirile ukleba)
- UN — magnolia hotel tbilisi reviews · hotel magnolia tbilisi by calora group · магнолия отель тбилиси · სასტუმრო მაგნოლია თბილისი

### F02 · Head-term: სასტუმრო თბილისში (კატეგორია)
- UN `q-head-en` — tbilisi hotels / hotels in tbilisi / boutique hotel tbilisi
- UN `q-head-ru` — отели тбилиси / бутик отель тбилиси
- UN `q-head-ka` — სასტუმროები თბილისში

### F03 · ბუტიკ-სასტუმრო ძველ თბილისში
- OQ `q-oldtown-en` — boutique hotel old tbilisi · hotel in old town tbilisi · small hotel old tbilisi
- OQ `q-oldtown-ka` — სასტუმრო ძველ თბილისში
- OQ `q-oldtown-ru` — бутик отель старый тбилиси (+ с парковкой)

### F04 · ახლოს ორიენტირთან / უბანი
- OQ `q-near-freedom-en` — hotel near freedom square tbilisi
- OQ `q-near-freedom-ru` — гостиница тбилиси центр площадь свободы недорого
- UN — hotel sololaki · hotel mtatsminda tbilisi · hotel near rustaveli avenue · walking distance old town tbilisi hotel

### F05 · პარკინგი (დიფერენციატორი)
- OQ `q-parking-en` — tbilisi hotel with free parking old town
- UN — where to park in old tbilisi · can I drive to old tbilisi hotel · отель тбилиси с бесплатной парковкой в центре

### F06 · ხასიათი: სახურავი, ხედი, მაგნოლია, ისტორიული სახლი
- OQ `q-rooftop-en` — tbilisi hotel rooftop terrace view old town
- UN — hotel with mtatsminda view · historic house hotel tbilisi · 127 year old magnolia tbilisi

### F07 · ოთახები და ფასები
- UN `q-rooms-en` — magnolia tbilisi room prices · cheap boutique hotel tbilisi · double room tbilisi with breakfast
- UN — how much is a hotel in tbilisi per night

### F08 · ოთახის მორგება (ოჯახი / მარტო)
- OQ `q-family-en` — family hotel tbilisi triple room city center
- UN — single room hotel tbilisi solo traveller · hotel tbilisi for 3 adults

### F09 · პირდაპირი ჯავშანი vs OTA
- UN `q-direct-en` — is it cheaper to book direct than booking.com · hotel tbilisi pay at hotel no prepayment

### F10 · ჩამოსვლამდე წესები (support)
- UN `q-policies-en` — magnolia tbilisi check in time · cancellation policy · breakfast time · are pets allowed · children policy · payment by card

### F11 · აეროპორტი და გადაადგილება
- OQ `q-airport-en` — tbilisi airport to old town taxi bolt price how long
- UN — does the hotel offer airport transfer · nearest metro to hotel

### F12 · სად დავრჩე თბილისში (awareness)
- OQ `q-where-stay-en` — where to stay in tbilisi first time sololaki old town
- UN — best area to stay in tbilisi with a car · quiet area to stay in tbilisi near old town

### F13 · მიმოხილვები / ნდობა
- UN `q-reviews-en` — magnolia hotel tbilisi reviews · is magnolia tbilisi good

### F14–F15 · ლოკალები
- F14 RU: `q-head-ru`, `q-oldtown-ru`, `q-near-freedom-ru` (იხ. ზემოთ) — საჭიროებს RU გვერდებს
- F15 KA: `q-oldtown-ka`, `q-head-ka`

### F16 · AI პრომპტები (AEO ჰიპოთეზები — `ai-search-research`-ში გადასამოწმებელი)
- AI `q-ai-boutique-parking` — „Recommend a small boutique hotel in Old Tbilisi with free parking, walking distance to Freedom Square.“
- AI `q-ai-budget-couple` — „Charming hotel in Tbilisi old town for a couple under 150 GEL a night, with breakfast?“
- AI `q-ai-family` — „Family-friendly hotel in central Tbilisi with a room for three and parking?“
- AI `q-ai-brand-facts` — „Tell me about Hotel Magnolia Tbilisi — is it good, where is it, does it have parking?“

## 5. სანდოობა და რისკები
- მოცულობა/სირთულე: **უცნობია** ყველა ოჯახისთვის — რიგითობა ეყრდნობა ბიზნეს-ფიტს, intent-ს და SERP-ის სახეობას, არა რიცხვებს.
- ვერიფიცირებული არ არის: JSON-LD-ის არარსებობა, RU/KA მოთხოვნის ზომა, უბნის ზუსტი სახელი, Booking.com-ის 10/10 და მიმოხილვების რაოდენობა.
- **განახლების ტრიგერი:** GSC/Keyword Planner წვდომის მიღება; −25% შეთავაზების დასრულება; RU/KA ვერსიების გაშვება; OTA რეიტინგის ცვლილება; 2027 სეზონის დაწყება (მარტი).

## წყაროები (წვდომა 2026-10-27)
- [magnoliatbilisi.com](https://magnoliatbilisi.com/) · [/rooms](https://magnoliatbilisi.com/rooms) · [/our-hotel](https://magnoliatbilisi.com/our-hotel) · [/explore](https://magnoliatbilisi.com/explore) · [/contact](https://magnoliatbilisi.com/contact) · [robots.txt](https://magnoliatbilisi.com/robots.txt) · [sitemap.xml](https://magnoliatbilisi.com/sitemap.xml) · [llms.txt](https://magnoliatbilisi.com/llms.txt)
- [Expedia UK — Magnolia h79660622](https://www.expedia.co.uk/Tbilisi-Hotels-Magnolia.h79660622.Hotel-Information) · [Hotels.com — Magnolia ho2550139904](https://kr.hotels.com/en/ho2550139904/magnolia-tbilisi-georgia/)
- [Kayak — hotels near Freedom Square](https://www.kayak.com/Tbilisi-Hotels_Freedom-Square.L51708.24544.hotel.ksp) · [JAMnews — Hotel-boutique "Old Tbilisi"](https://jam-news.net/hotel-boutique-old-tbilisi/) · [Accor — Tbilisi hotels with parking](https://all.accor.com/a/fr/destination/city/hotels-tbilisi-v3958/parking.html)
- [The World Was Here First — Where to stay in Tbilisi](https://www.theworldwasherefirst.com/where-to-stay-in-tbilisi-georgia/) · [Red Fedora Diary — where to stay](https://www.redfedoradiary.com/where-to-stay-in-tbilisi/) · [Wego — Tbilisi airport transfer](https://blog.wego.com/tbilisi-airport-transfer/)
- [101hotels — недорогие гостиницы в центре Тбилиси](https://101hotels.com/georgia/tbilisi/inexpensive_center) · [Wikipedia — Mtatsminda District](https://en.wikipedia.org/wiki/Mtatsminda_District)
