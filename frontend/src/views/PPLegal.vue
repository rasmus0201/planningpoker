<script lang="ts" setup>
import { ref } from "vue";

const page = ref<"privacy" | "cookie">("privacy");
</script>

<template>
  <div class="columns py-4">
    <div class="column">
      <div class="content">
        <h1 class="is-size-1 has-text-weight-bold">Legal</h1>
        <div class="is-flex mb-5">
          <div class="mr-2">
            <button class="button is-large" :class="{ 'is-info': page === 'privacy' }" @click="page = 'privacy'">
              Privatlivspolitik
            </button>
          </div>
          <div class="ml-2">
            <button class="button is-large" :class="{ 'is-info': page === 'cookie' }" @click="page = 'cookie'">
              Cookiepolitik
            </button>
          </div>
        </div>

        <div v-if="page === 'privacy'">
          <h3 class="is-size-3">Dataansvarlig</h3>
          <p>
            Vi er dataansvarlig for behandlingen af de personoplysninger, som vi behandler om vores kunder og
            samarbejdspartnere. Du finder vores kontaktoplysninger nedenfor.
            <br /><br />
            <span>Ordbogen A/S</span>
            <br />
            <span>support [snabel-a] ordbogen [dot] com</span>
            <br /><br />
            Hvis du har spørgsmål til behandlingen af dine personoplysninger, så kan du kontakte os via ovenstående
            email.
          </p>

          <h3 class="is-size-3">Behandlingsaktiviteter</h3>
          <p>Som dataansvarlig jf. GDPR, så har vi følgende behandlingsaktiviteter.</p>
          <h4 class="is-size-4">Besøg på hjemmeside</h4>
          <p>
            Når du besøger vores hjemmeside, så anvender vi cookies for at hjemmesiden kan fungere, hvilket du kan læse
            mere om i vores <a href="#" @click.prevent="page = 'cookie'">cookiepolitik</a>.
          </p>
          <h4 class="is-size-4">Kommunikation med brugere og potentielle kunder</h4>
          <p>
            Når du har spørgsmål til vores side, eller ønsker at høre mere om vores services, så kan du kontakte os via
            E-mail.
          </p>
          <p>
            Herigennem vil vi behandle dine personoplysninger, så vi kan indgå i en dialog med dig fx svare på spørgsmål
            om vores ydelser. Vi behandler kun den information, som du giver os i forbindelse med vores kommunikation.
          </p>
          <p>Vi vil typisk behandle følgende almindelige oplysninger: navn, email, telefonnummer.</p>
          <p>
            Vores hjemmel til at behandle disse personoplysninger er databeskyttelsesforordningens artikel 6, stk. 1
            litra f.
          </p>
          <p>Vi sletter vores kommunikation med dig når det står klart om du ønsker vores ydelser eller ej.</p>
          <p>
            Skulle der i et særligt tilfælde opstå et behov for at opbevare dine personoplysninger i længere tid, så vil
            dette kunne være tilfældet.
          </p>

          <h3 class="is-size-3">Databehandlere</h3>
          <p>
            Få kan klare alt selv, og det samme gælder os. Vi har derfor samarbejdspartnere, samt benytter os af
            leverandører, hvoraf nogle kan være databehandlere.
          </p>
          <p>
            Eksterne leverandører kan eksempelvis levere systemer til at organisere vores arbejde, services, rådgivning,
            IT-hosting eller markedsføring, el.l.
          </p>
          <p>
            Det er vores ansvar at sikre, at dine personoplysninger behandles ordentligt. Derfor stiller vi høje krav
            til vores samarbejdspartnere, og vores partnere skal garantere, at dine personoplysninger er beskyttet.
          </p>
          <p>
            Vi indgår derfor aftaler herom med virksomheder (databehandlere), der håndterer personoplysninger på vores
            vegne for at højne sikkerheden af dine personoplysninger.
          </p>

          <h3 class="is-size-3">Databehandlere</h3>
          <p>Vi videregiver ikke dine personoplysninger til tredjemand.</p>

          <h3 class="is-size-3">Profilering og automatiserede afgørelser</h3>
          <p>Vi foretager ikke profilering eller automatiserede afgørelser.</p>

          <h3 class="is-size-3">Tredjelandeoverførsler</h3>
          <p>Vi benytter som udgangspunkt databehandlere i EU/EØS, eller som opbevarer data i EU/EØS.</p>
          <p>
            I nogle tilfælde er dette ikke muligt, og her kan der benyttes databehandlere udenfor EU/EØS, hvis disse kan
            give dine personoplysninger en passende beskyttelse.
          </p>

          <h3 class="is-size-3">Behandlingssikkerhed</h3>
          <p>
            Vi holder behandlingen af personoplysninger sikker ved at have indført passende tekniske og organisatoriske
            foranstaltninger.
          </p>

          <h3 class="is-size-3">De registreredes rettigheder</h3>
          <p>
            Du har efter databeskyttelsesforordningen en række rettigheder i forhold til vores behandling af oplysninger
            om dig.
          </p>
          <p>Hvis du vil gøre brug af dine rettigheder skal du kontakte os, så vi kan hjælpe dig med dette.</p>

          <h4 class="is-size-4">Ret til at se oplysninger (indsigtsret)</h4>
          <p>
            Du har ret til at få indsigt i de oplysninger, som vi behandler om dig, samt en række yderligere
            oplysninger.
          </p>

          <h4 class="is-size-4">Ret til berigtigelse (rettelse)</h4>
          <p>Du har ret til at få urigtige oplysninger om dig selv rettet.</p>

          <h4 class="is-size-4">Ret til sletning</h4>
          <p>
            I særlige tilfælde har du ret til at få slettet oplysninger om dig, inden tidspunktet for vores almindelige
            generelle sletning indtræffer.
          </p>

          <h4 class="is-size-4">Ret til begrænsning af behandling</h4>
          <p>
            Du har i visse tilfælde ret til at få behandlingen af dine personoplysninger begrænset. Hvis du har ret til
            at få begrænset behandlingen, må vi fremover kun behandle oplysningerne – bortset fra opbevaring – med dit
            samtykke, eller med henblik på at retskrav kan fastlægges, gøres gældende eller forsvares, eller for at
            beskytte en person eller vigtige samfundsinteresser.
          </p>

          <h4 class="is-size-4">Ret til indsigelse</h4>
          <p>
            Du har i visse tilfælde ret til at gøre indsigelse mod vores ellers lovlige behandling af dine
            personoplysninger. Du kan også gøre indsigelse mod behandling af dine oplysninger til direkte markedsføring.
          </p>

          <h4 class="is-size-4">Ret til at transmittere oplysninger (dataportabilitet)</h4>
          <p>
            Du har i visse tilfælde ret til at modtage dine personoplysninger i et struktureret, almindeligt anvendt og
            maskinlæsbart format samt at få overført disse personoplysninger fra én dataansvarlig til en anden uden
            hindring.
          </p>
          <p>
            Du kan læse mere om dine rettigheder i Datatilsynets vejledning om de registreredes rettigheder, som du
            finder på <a href="https://datatilsynet.dk">www.datatilsynet.dk</a>.
          </p>

          <h4 class="is-size-4">Tilbagetrækning af samtykke</h4>
          <p>
            Når vores behandling af dine personoplysninger er baseret på dit samtykke, så har du ret til at trække dit
            samtykke tilbage.
          </p>

          <h4 class="is-size-4">Klage til Datatilsynet</h4>
          <p>
            Du har ret til at indgive en klage til Datatilsynet, hvis du er utilfreds med den måde, vi behandler dine
            personoplysninger på. Du finder Datatilsynets kontaktoplysninger på
            <a href="https://datatilsynet.dk">www.datatilsynet.dk</a>.
          </p>
          <p>Vi vil generelt opfordre dig til at læse mere om GDPR, så du er opdateret på reglerne.</p>
        </div>

        <div v-if="page === 'cookie'">
          <h3 class="is-size-3">Cookies</h3>
          <p>
            Websitet anvender "cookies", der er en tekstfil, som gemmes på din computer, mobil el. tilsvarende med det
            formål at genkende den, huske indstillinger og udføre statistik. Cookies kan ikke indeholde skadelig kode
            som f.eks. virus.
          </p>
          <p>
            Det er muligt at slette eller blokere for cookies. Se vejledning:
            <a href="https://minecookies.org/cookiehandtering/">https://minecookies.org/cookiehandtering/</a>.
          </p>
          <p>
            Hvis du sletter eller blokerer cookies kan du risikere at websitet ikke fungerer optimalt samt at der er
            indhold, du ikke kan få adgang til.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped></style>
