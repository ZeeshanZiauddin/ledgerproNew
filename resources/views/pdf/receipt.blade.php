<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        *,
        ::before,
        ::after {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: #e5e7eb;
        }

        ::before,
        ::after {
            --tw-content: '';
        }

        html {
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -moz-tab-size: 4;
            tab-size: 4;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-feature-settings: normal;
            font-variation-settings: normal;
        }

        body {
            margin: 0;
            line-height: inherit;
        }

        hr {
            height: 0;
            color: inherit;
            border-top-width: 1px;
        }

        abbr:where([title]) {
            -webkit-text-decoration: underline dotted;
            text-decoration: underline dotted;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-size: inherit;
            font-weight: inherit;
        }

        a {
            color: inherit;
            text-decoration: inherit;
        }

        b,
        strong {
            font-weight: bolder;
        }

        code,
        kbd,
        samp,
        pre {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 1em;
        }

        small {
            font-size: 80%;
        }

        sub,
        sup {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: baseline;
        }

        sub {
            bottom: -0.25em;
        }

        sup {
            top: -0.5em;
        }

        table {
            text-indent: 0;
            border-color: inherit;
            border-collapse: collapse;
        }

        button,
        input,
        optgroup,
        select,
        textarea {
            font-family: inherit;
            font-feature-settings: inherit;
            font-variation-settings: inherit;
            font-size: 100%;
            font-weight: inherit;
            line-height: inherit;
            color: inherit;
            margin: 0;
            padding: 0;
        }

        button,
        select {
            text-transform: none;
        }

        button,
        [type='button'],
        [type='reset'],
        [type='submit'] {
            appearance: button;
            -webkit-appearance: button;
            background-color: transparent;
            background-image: none;
        }

        :-moz-focusring {
            outline: auto;
        }

        :-moz-ui-invalid {
            box-shadow: none;
        }

        progress {
            vertical-align: baseline;
        }

        ::-webkit-inner-spin-button,
        ::-webkit-outer-spin-button {
            height: auto;
        }

        [type='search'] {
            appearance: textfield;
            -webkit-appearance: textfield;
            outline-offset: -2px;
        }

        ::-webkit-search-decoration {
            -webkit-appearance: none;
        }

        ::-webkit-file-upload-button {
            -webkit-appearance: button;
            font: inherit;
        }

        summary {
            display: list-item;
        }

        blockquote,
        dl,
        dd,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        hr,
        figure,
        p,
        pre {
            margin: 0;
        }

        fieldset {
            margin: 0;
            padding: 0;
        }

        legend {
            padding: 0;
        }

        ol,
        ul,
        menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        dialog {
            padding: 0;
        }

        textarea {
            resize: vertical;
        }

        input::placeholder,
        textarea::placeholder {
            opacity: 1;
            color: #9ca3af;
        }

        button,
        [role="button"] {
            cursor: pointer;
        }

        :disabled {
            cursor: default;
        }

        img,
        svg,
        video,
        canvas,
        audio,
        iframe,
        embed,
        object {
            display: block;
        }

        img,
        video {
            max-width: 100%;
            height: auto;
        }

        [hidden] {
            display: none;
        }

        .fixed {
            position: fixed;
        }

        .bottom-0 {
            bottom: 0px;
        }

        .left-0 {
            left: 0px;
        }

        .table {
            display: table;
        }

        .h-12 {
            height: 3rem;
        }

        .w-1\/2 {
            width: 50%;
        }

        .w-full {
            width: 100%;
        }

        .border-collapse {
            border-collapse: collapse;
        }

        .border-spacing-0 {
            --tw-border-spacing-x: 0px;
            --tw-border-spacing-y: 0px;
            border-spacing: var(--tw-border-spacing-x) var(--tw-border-spacing-y);
        }

        .whitespace-nowrap {
            white-space: nowrap;
        }

        .border-b {
            border-bottom-width: 1px;
        }

        .border-b-2 {
            border-bottom-width: 2px;
        }

        .border-r {
            border-right-width: 1px;
        }

        .border-main {
            border-color: #5c6ac4;
        }

        .border-secondary {
            border-color: #94a3b8;
        }

        .bg-main {
            background-color: #5c6ac4;
        }

        .bg-slate-100 {
            background-color: #f1f5f9;
        }

        .p-3 {
            padding: 0.75rem;
        }

        .px-14 {
            padding-left: 3.5rem;
            padding-right: 3.5rem;
        }

        .px-2 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .py-10 {
            padding-top: 2.5rem;
            padding-bottom: 2.5rem;
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .py-6 {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .pb-3 {
            padding-bottom: 0.75rem;
        }

        .pl-2 {
            padding-left: 0.5rem;
        }

        .pl-3 {
            padding-left: 0.75rem;
        }

        .pl-4 {
            padding-left: 1rem;
        }

        .pr-3 {
            padding-right: 0.75rem;
        }

        .pr-4 {
            padding-right: 1rem;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .align-top {
            vertical-align: top;
        }

        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .italic {
            font-style: italic;
        }

        .text-main {
            color: #5c6ac4;
        }

        .text-neutral-600 {
            color: #525252;
        }

        .text-neutral-700 {
            color: #404040;
        }

        .text-slate-300 {
            color: #cbd5e1;
        }

        .text-slate-400 {
            color: #94a3b8;
        }

        .text-white {
            color: #fff;
        }

        @page {
            margin: 0;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div>
        <div class="py-4">
            <div class="px-14 py-6">
                <table class="w-full border-collapse border-spacing-0">
                    <tbody>
                        <tr>
                            <td class="w-full align-top">
                                <div>
                                    <img src="images/outlook-logo.png" class="h-12" />
                                </div>
                            </td>

                            <td class="align-top">
                                <div class="text-sm">
                                    <table class="border-collapse border-spacing-0">
                                        <tbody>
                                            <tr>
                                                <td class="border-r pr-4">
                                                    <div>
                                                        <p class="whitespace-nowrap text-slate-400 text-right">Date</p>
                                                        <p class="whitespace-nowrap font-bold text-main text-right">
                                                            {{$receipt['date']}}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="pl-4">
                                                    <div>
                                                        <p class="whitespace-nowrap text-slate-400 text-right">Invoice #
                                                        </p>
                                                        <p class="whitespace-nowrap font-bold text-main text-right">
                                                            {{$receipt['name']}}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <header class="text-center  px-14" style="padding-top:20px;">
                    <h2 class="text-main font-bold">Receipt Customer Copy</h2>
                </header>
            </div>

            <div class="bg-slate-100 px-14 py-6 text-sm">
                <table class="w-full border-collapse border-spacing-0">
                    <tbody>
                        <tr>
                            <td class="w-1/2 align-top">
                                <div class="text-sm text-neutral-600">
                                    <p class="font-bold">Receipt Details</p>
                                    <p>Receipt No: {{$receipt['name']}}</p>
                                    <p>Receipt From: {{$customer}}</p>
                                    <p>Recieved a sum of {{'(' . $currency . ')'}}: <span
                                            class="font-bold text-main">{{$receipt['totalInWords']}}</span>
                                    </p>
                                    <p>As payment against Invoice/Card No: <span
                                            class="font-bold text-main">{{$receipt['card_name']}}</span></p>
                                </div>
                            </td>
                            <td class="w-1/2 align-top text-right">
                                <div class="text-sm text-neutral-600">
                                    <p class="font-bold">Payment Details</p>
                                    <p>Mode: {{$receipt['mode']}}</p>
                                    <p>Bank: {{$bank}}</p>
                                    <p>Chq/DC/CC No: {{$receipt['cheque']}}</p>
                                    <p>Ref No: </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-14 py-2 text-sm text-neutral-700">
                <table class="w-full border-collapse border-spacing-0">
                    <tbody>
                        <tr>
                            <td colspan="7">
                                <table class="w-full border-collapse border-spacing-0">
                                    <tbody>
                                        <tr>
                                            <td class="w-full"></td>
                                            <td>
                                                <table class="w-full border-collapse border-spacing-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="border-b p-3">
                                                                <div class="whitespace-nowrap text-slate-400">Amount:
                                                                </div>
                                                            </td>
                                                            <td class="border-b p-3 text-right">
                                                                <div class="whitespace-nowrap font-bold text-main">
                                                                    {{$currency . $receipt['total']}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-3">
                                                                <div class="whitespace-nowrap text-slate-400">Charges:
                                                                </div>
                                                            </td>
                                                            <td class="p-3 text-right">
                                                                <div class="whitespace-nowrap font-bold text-main">
                                                                    {{$currency . ($receipt['charges'] ?? '0.00')}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bg-main p-3">
                                                                <div class="whitespace-nowrap font-bold text-white">
                                                                    Net Total:</div>
                                                            </td>
                                                            <td class="bg-main p-3 text-right">
                                                                <div class="whitespace-nowrap font-bold text-white">
                                                                    {{$currency . ($receipt['total'] + $receipt['charges'])}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <div class=" px-14 py-6">
                <div class="border-b-2 border-main pb-3">
                    <table style="width: 100%;">
                        <tr>
                            <td style="text-align: left;">
                                <p>Received by: <span
                                        class="border-b-2 border-secondary text-main">{{$issued_by}}</span></p>
                            </td>
                            <td style="text-align: right;">
                                <p>Checked by: <span
                                        class="border-b-2 border-secondary font-bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class=" px-14 py-6 text-sm text-neutral-700">
                <p class="text-main font-bold">CANCELLATION & AMENDMENT CHARGES:</p>
                <p class="italic text-xs">Date Changes Before Departure {{'        '}}- Not
                    Permitted</p>
                <p class="italic text-xs">Date Changes Before Inbound Departure {{'        '}} -
                    Not Permitted</p>
                <p class="italic text-xs">Cancellation Fees Before Departure {{'        '}} - No
                    Refunds</p>
                <p class="italic text-xs">Cancellation Fees After Departure / No Shows / Partly
                    Used
                    Tickets{{'        '}} - No
                    Refunds</p>
                <p class="text-xs"> All changes/amendments are subject to fare
                    restrictions/seasons and seat
                    availability at the time of
                    making the amendment.</p>
                <p class="text-xs"> This booking is accepted subject to the full terms &
                    conditions</p>
                <p class="text-main font-bold "> Important Notice:</p>
                <p class="text-xs"> First & Business Class passengers are requested to check in
                    at least 2 hrs before
                    departure.
                    Economy Class passengers are requested to check in at least 3 hrs before
                    departure.</p>
                <p class="text-xs">Passport should be valid for at least 6 months from the date
                    you return to the U.K.
                </p>
                <p class="text-xs"> with enough clean pages for the country/ies you are
                    visiting. Most countries now
                    require a
                    machine-readable passport.
                    Many countries require a visa to enter. Please ensure that your passport is
                    valid and up to date for
                    the
                    country/ies
                    you are visiting and that all requirements for visas are met. Failure to do
                    so may result in you
                    being
                    refused to
                    travel in which case no refund will be payable. If you are not a British
                    passport holder then the
                    conditions may be different
                    than the norm. Your travel agent or the relevant Embassy/Consulate of the
                    country/ies you are
                    visiting
                    will be able to advise
                    and assist with this.
                </p>

                <p class="text-main font-bold">General Terms and Conditions:</p>

                <ol class="text-xs">
                    <li>Once ticket is issued any change in travelling dates or cancellation is
                        subject to a
                        cancellation
                        charge and availability of
                        new dates. The fare you have purchased may not allow changes or may
                        carry penalties to do so. If
                        you
                        fail to travel on your
                        confirmed dates, you may lose the whole amount paid or may have to pay a
                        cancellation/no show
                        penalty.</li>
                    <li>Refunds of tickets are subject to a processing time of 8 to 12 weeks.
                    </li>
                    <li>For more details, please refer to our Terms and Conditions</li>
                </ol>
                <p class="italic font-bold"> Your ATOL Financial Protection</p>
                <p class="italic text-xs"> For your financial protection all monies paid to us
                    are ATOL protected. ATOL
                    is a
                    financial
                    protection
                    scheme for
                    holidaymakers operated by the CAA (Civil Aviation Authority).</p>
                <p class="italic text-xs">The ATOL scheme ensures you do not lose the money you
                    have paid us and are not
                    stranded abroad. Our
                    ATOL
                    number is
                    5553.
                </p>
            </div>

            <footer class="fixed bottom-0 left-0 bg-slate-100 w-full text-neutral-600 text-center text-xs py-3">
                {{$company['name']}}
                <span class="text-slate-300 px-2">|</span>
                {{$company['email']}}

                <span class="text-slate-300 px-2">|</span>
                {{$company['phone']}}

            </footer>
        </div>
        <div class="py-4">
            <div class="px-14 py-6">
                <table class="w-full border-collapse border-spacing-0">
                    <tbody>
                        <tr>
                            <td class="w-full align-top">
                                <div>
                                    <img src="images/outlook-logo.png" class="h-12" />
                                </div>
                            </td>

                            <td class="align-top">
                                <div class="text-sm">
                                    <table class="border-collapse border-spacing-0">
                                        <tbody>
                                            <tr>
                                                <td class="border-r pr-4">
                                                    <div>
                                                        <p class="whitespace-nowrap text-slate-400 text-right">Date</p>
                                                        <p class="whitespace-nowrap font-bold text-main text-right">
                                                            {{$receipt['date']}}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="pl-4">
                                                    <div>
                                                        <p class="whitespace-nowrap text-slate-400 text-right">Receipt #
                                                        </p>
                                                        <p class="whitespace-nowrap font-bold text-main text-right">
                                                            {{$receipt['name']}}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <header class="text-center  px-14" style="padding-top:20px;">
                    <h2 class="text-main font-bold">Receipt Office Copy</h2>
                </header>
            </div>

            <div class="bg-slate-100 px-14 py-6 text-sm">
                <table class="w-full border-collapse border-spacing-0">
                    <tbody>
                        <tr>
                            <td class="w-1/2 align-top">
                                <div class="text-sm text-neutral-600">
                                    <p class="font-bold">Receipt Details</p>
                                    <p>Receipt No: {{$receipt['name']}}</p>
                                    <p>Receipt From: {{$customer}}</p>
                                    <p>Recieved a sum of {{'(' . $currency . ')'}}: <span
                                            class="font-bold text-main">{{$receipt['totalInWords']}}</span>
                                    </p>
                                    <p>As payment against Invoice/Card No: <span
                                            class="font-bold text-main">{{$receipt['card_name']}}</span></p>
                                </div>
                            </td>
                            <td class="w-1/2 align-top text-right">
                                <div class="text-sm text-neutral-600">
                                    <p class="font-bold">Payment Details</p>
                                    <p>Mode: {{$receipt['mode']}}</p>
                                    <p>Bank: {{$bank}}</p>
                                    <p>Chq/DC/CC No: {{$receipt['cheque']}}</p>
                                    <p>Ref No: </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-14 py-2 text-sm text-neutral-700">
                <table class="w-full border-collapse border-spacing-0">
                    <tbody>
                        <tr>
                            <td colspan="7">
                                <table class="w-full border-collapse border-spacing-0">
                                    <tbody>
                                        <tr>
                                            <td class="w-full"></td>
                                            <td>
                                                <table class="w-full border-collapse border-spacing-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="border-b p-3">
                                                                <div class="whitespace-nowrap text-slate-400">Amount:
                                                                </div>
                                                            </td>
                                                            <td class="border-b p-3 text-right">
                                                                <div class="whitespace-nowrap font-bold text-main">
                                                                    {{$currency . $receipt['total']}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-3">
                                                                <div class="whitespace-nowrap text-slate-400">Charges:
                                                                </div>
                                                            </td>
                                                            <td class="p-3 text-right">
                                                                <div class="whitespace-nowrap font-bold text-main">
                                                                    {{$currency . ($receipt['charges'] ?? '0.00')}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bg-main p-3">
                                                                <div class="whitespace-nowrap font-bold text-white">
                                                                    Net Total:</div>
                                                            </td>
                                                            <td class="bg-main p-3 text-right">
                                                                <div class="whitespace-nowrap font-bold text-white">
                                                                    {{$currency . ($receipt['total'] + $receipt['charges'])}}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <div class=" px-14 py-6">
                <div class="border-b-2 border-main pb-3">
                    <table style="width: 100%;">
                        <tr>
                            <td style="text-align: left;">
                                <p>Received by: <span
                                        class="border-b-2 border-secondary text-main">{{$issued_by}}</span></p>
                            </td>
                            <td style="text-align: right;">
                                <p>Checked by: <span
                                        class="border-b-2 border-secondary font-bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <footer class="fixed bottom-0 left-0 bg-slate-100 w-full text-neutral-600 text-center text-xs py-3">
                {{$company['name']}}
                <span class="text-slate-300 px-2">|</span>
                {{$company['email']}}

                <span class="text-slate-300 px-2">|</span>
                {{$company['phone']}}

            </footer>
        </div>
    </div>
</body>

</html>