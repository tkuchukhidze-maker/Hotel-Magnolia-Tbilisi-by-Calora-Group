# Keyword decisions — ორი ტრეკი, რიგითობა, უარყოფილი იდეები

**სტატუსი:** provisional · **თარიღი:** 2026-10-27 · **მფლობელი:** Calora Group (marketing + ops + dev)
**წინა:** [content-gap.md](content-gap.md) · **კორპუსი:** [query-corpus.json](query-corpus.json)

## 1. გადაწყვეტილება მოკლედ

Magnolia-ს საიტი ფლობს კარგ ფაქტებს, მაგრამ **ბრენდულ ძიებაზეც კი ჩანს OTA-ები**, ხოლო AI სისტემები ხედავენ placeholder-ებს და რეიტინგის წინააღმდეგობას. ამიტომ რიგითობა: (1) ფაქტების და ერთეულის ჭეშმარიტება → (2) დიფერენციატორების (პარკინგი, ლოკაცია, სახურავი, ოჯახი) სრული პასუხები → (3) head-term-ის ლიდერობა Google Hotels/GBP-ის და ავტორიტეტის მეშვეობით → (4) RU/KA.

## 2. Head-term leadership track

| ოჯახი | მფლობელი | მიზანი | ძირითადი ხარვეზი წამყვან შედეგებთან | საჭირო სამუშაო | ეტაპები |
|---|---|---|---|---|---|
| **F02** tbilisi hotels / hotels in tbilisi / boutique hotel tbilisi / отели тбилиси | `/` (+ GBP, Google Hotels) | top-3 ხილვადობა Google Hotels/local მოდულში თბილისის ძველი ქალაქის კონტექსტში; პოზიცია 1 = stretch. ორგანულ სიაში intent = OTA სიები, ამიტომ ერთ სასტუმროს მთავარი გზა არის hotel/local მოდული | OTA-ებს აქვთ სიები, ფილტრები, ათასობით მიმოხილვა; Magnolia-ს — 36 მიმოხილვა OTA-ზე, schema არ ჩანს, GBP კავშირი უცნობია | GBP სრული პროფილი; Google Hotel Center free booking links (პირდაპირი ფასი); `Hotel` schema; რეალური მიმოხილვების ზრდა; ადგილობრივი მედია/გიდები (ავთენტური ბმულები) | now: GBP+schema+ფაქტები → next: Hotel Center, მიმოხილვების პროგრამა → later: top-20 → top-10 → top-3 დათარიღებული შემოწმებით |
| **F03** boutique hotel old tbilisi (+ka/ru) | `/` | top-3 en ორგანულ/ლოკალურ შედეგებში | კონკურენტის სახელი „Boutique Old Tbilisi“; ჩვენი ტექსტი ზოგადი | H1/intro ფაქტებით (უბანი, 1899, მაგნოლია, პარკინგი), schema, ფოტოები alt-ით | now → next |

⚠️ რომელი head-term-ია ყველაზე დიდი მოცულობის — **დაუდგენელია** (იხ. § 5). F02 რჩება ტრეკში იმის მიუხედავად, რომ რთულია; სირთულე ცვლის მარშრუტს, არა ამბიციას.

## 3. Near-term capture track

| პრიორიტეტი | ოჯახი | ზოლი | ქმედება | მფლობელი |
|---|---|---|---|---|
| **now** | F10 + A1/A2 | coverage gap (blocker) | ყველა placeholder-ის შევსება; HTML და `/llms.txt` ერთ რევიზიაში | ops → content |
| **now** | F13 + A3 | trust / defend | რეალური მიმოხილვები (სახელი, ქვეყანა, წყარო, თარიღი); რეიტინგი რაოდენობით და წყაროთი; 7.8-ის მიზეზების ანალიზი OTA-ზე | marketing |
| **now** | F01 | defend / entity | GBP ↔ საიტი; `Hotel` schema + `sameAs`; ერთი სახელის ფორმა ყველგან („Hotel Magnolia Tbilisi by Calora Group“); OTA-ზე ოფიციალური საიტის ბმული სადაც შესაძლებელია | marketing + dev |
| **now** | F05 | coverage gap (დიფერენციატორი) | /explore „Arriving by car & free parking“ სრული სექცია | ops + content |
| **now** | F04, F11 | coverage gap | წუთები ფეხით; უბნის სახელის დადასტურება; აეროპორტის სექცია დადასტურებული ტრანსფერით | content |
| **now** | F07 | snippet/CTR | ერთიანი „from“ ფასის წესი (B5) | content |
| next | F08 | coverage | Triple/Single ფოტოები, ბავშვების წესი | ops |
| next | F06 | coverage | ისტორია + სახურავის ფოტოები; alt ტექსტი | content |
| next | F14 RU | coverage gap (locale) | /ru/ + hreflang → `seo-hreflang` | content + dev |
| next | F12 | strategic build | ერთი გიდი „where to stay in Tbilisi (with a car)“ ორიგინალური მონაცემით | content |
| next | F16 | measurement | `ai-search-research` Research Pack + `ai-visibility-monitor` baseline 4 AI პრომპტზე | marketing |
| later | F15 KA | coverage | /ka/ ვერსია | content |

## 4. უარყოფილი / გადადებული იდეები (defer)
- **„best hotel in Tbilisi“** — defer: დასაბუთების გარეშე მტკიცება (misleading claim). F02 ფარავს რეალურ საჭიროებას.
- **„luxury hotel Tbilisi“ / „5 star hotel Tbilisi“** — defer: პროდუქტის შეუსაბამობა (OTA კლასი 3.5; ფასი GEL 106–190).
- **ცალკე გვერდი თითო ორიენტირზე** (Narikala, Peace Bridge…) — defer: თხელი გვერდების რისკი; ფარავს /explore.
- **„cheap hotel Tbilisi“ / hostel** — defer როგორც primary: ფასის სეგმენტი ნაწილობრივ ემთხვევა (Single GEL 106), მაგრამ ბრენდის პოზიციონირება ბუტიკია; რჩება F07-ის მხარდამჭერ ენად.

## 5. Research dependencies
1. **Google Search Console** (საკუთრების დადასტურება magnoliatbilisi.com-ზე) — 28 დღის შემდეგ: query/page/country/device ექსპორტი → `search-console-data-review`.
2. **Google Keyword Planner** (en: GE, US, GB, IL, DE; ru: GE, KZ, AM, IL, RU; ka: GE) — F02, F03, F04, F05 ოჯახების მოცულობა, მონიშნული provider/თარიღით.
3. **Google Trends** — სეზონურობა „tbilisi hotel“ (შედარებითი ინდექსი, არა მოცულობა).
4. **ლოკალიზებული SERP** (Google, GE, mobile, en/ru/ka) — F02/F03/F04 დათარიღებული სნეპშოტები, Hotel pack-ის ჩათვლით.
5. **მფლობელის დადასტურებები:** უბნის სახელი; Booking.com რეიტინგი და რაოდენობა; ყველა placeholder; ტრანსფერი; პარკინგის დეტალები.

## 6. გაზომვა და განახლება
- Baseline: GSC (ჩართვის თარიღიდან) + დათარიღებული SERP შემოწმება F01/F02/F03-ზე; AI baseline ცალკე.
- ეტაპები head-term-ზე: ინდექსირებული მფლობელი → impressions → top-20 → top-10 → top-3 (ხილვადობის ზოლები, არა პროგნოზი ან გარანტია).
- განახლება: შეთავაზების დასრულება, ფასების ცვლილება, RU/KA გაშვება, OTA რეიტინგის ცვლილება, 2027 სეზონის დაწყება.

## 7. Handoff
`seo-schema` (Hotel markup) · `seo-local` (GBP) · `seo-hreflang` (RU/KA) · `seo-content` (home/explore/contact ტექსტები) · `ai-search-research` (F16) · `seo-action-plan` (თიკეტები).
