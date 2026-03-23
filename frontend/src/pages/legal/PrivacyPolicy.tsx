import { Link } from "react-router";
import { LegalPage } from "@/components/welcome/LegalPage";

export function PrivacyPolicy() {
    return (
        <LegalPage
            title="Privacy Policy"
            subtitle="How Smart Move AI collects, uses, and protects your personal information."
            lastUpdated="March 23, 2026"
            sections={[
                {
                    title: "Who We Are",
                    content: (
                        <p>
                            Smart Move AI ("we", "us", or "our") operates the Smart Move AI platform, an
                            AI-powered fitness planning service. This Privacy Policy explains how we collect,
                            use, disclose, and safeguard your information when you use our service. By
                            registering for an account, you agree to the practices described in this policy.
                        </p>
                    ),
                },
                {
                    title: "Information We Collect",
                    content: (
                        <>
                            <p>We collect the following categories of personal data:</p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>
                                    <strong>Account information:</strong> name, surname, and email address
                                    provided at registration.
                                </li>
                                <li>
                                    <strong>Health and fitness data:</strong> height, weight, age, gender,
                                    experience level, fitness goals, injuries, and exercise preferences you
                                    voluntarily provide to generate workout plans.
                                </li>
                                <li>
                                    <strong>Usage data:</strong> pages visited, features used, and interactions
                                    with generated workout plans.
                                </li>
                                <li>
                                    <strong>Payment information:</strong> billing details are processed directly
                                    by Stripe, Inc. We do not store card numbers or full payment credentials.
                                </li>
                                <li>
                                    <strong>Technical data:</strong> IP address, browser type, and device
                                    identifiers collected automatically via server logs.
                                </li>
                            </ul>
                        </>
                    ),
                },
                {
                    title: "How We Use Your Information",
                    content: (
                        <>
                            <p>We use your personal data to:</p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>Create and manage your account and authenticate your identity.</li>
                                <li>
                                    Generate personalized AI workout plans based on your fitness profile and
                                    preferences.
                                </li>
                                <li>Process subscription payments and manage billing through Stripe.</li>
                                <li>
                                    Send transactional emails (account verification, password reset, billing
                                    receipts).
                                </li>
                                <li>
                                    Improve the quality and accuracy of our AI-generated workout recommendations.
                                </li>
                                <li>Comply with applicable legal obligations.</li>
                            </ul>
                            <p className="mt-3">
                                We do not sell your personal data to third parties. We do not use your health
                                data for advertising purposes.
                            </p>
                        </>
                    ),
                },
                {
                    title: "Legal Basis for Processing (GDPR)",
                    content: (
                        <>
                            <p>
                                If you are located in the European Economic Area (EEA), we process your data
                                under the following legal bases:
                            </p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>
                                    <strong>Contract performance:</strong> processing necessary to provide the
                                    service you signed up for.
                                </li>
                                <li>
                                    <strong>Legitimate interests:</strong> improving our service, preventing
                                    fraud, and ensuring security.
                                </li>
                                <li>
                                    <strong>Consent:</strong> for health/fitness data, which constitutes special
                                    category data under GDPR. You provide explicit consent by voluntarily
                                    submitting this data.
                                </li>
                                <li>
                                    <strong>Legal obligation:</strong> when required by applicable law.
                                </li>
                            </ul>
                        </>
                    ),
                },
                {
                    title: "Data Sharing and Third Parties",
                    content: (
                        <>
                            <p>We share your data only with the following trusted service providers:</p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>
                                    <strong>Stripe, Inc.</strong> — payment processing. Stripe's privacy
                                    practices are governed by their own{" "}
                                    <a
                                        href="https://stripe.com/privacy"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-blue-600 hover:underline"
                                    >
                                        Privacy Policy
                                    </a>
                                    .
                                </li>
                                <li>
                                    <strong>AI providers:</strong> anonymized fitness preferences may be sent to
                                    AI processing services to generate your workout plan. No directly
                                    identifiable information (name, email) is transmitted.
                                </li>
                                <li>
                                    <strong>Cloud hosting:</strong> your data is stored on secure cloud
                                    infrastructure with encryption at rest and in transit.
                                </li>
                            </ul>
                            <p className="mt-3">
                                We may disclose your data if required by law, court order, or to protect the
                                rights and safety of our users.
                            </p>
                        </>
                    ),
                },
                {
                    title: "Cookies and Tracking",
                    content: (
                        <>
                            <p>We use the following types of cookies:</p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>
                                    <strong>Strictly necessary cookies:</strong> an HttpOnly refresh token
                                    cookie used to maintain your authenticated session. This cookie is
                                    essential for the service to function and does not require consent.
                                </li>
                                <li>
                                    <strong>Stripe cookies:</strong> Stripe may set cookies during payment
                                    processing for fraud prevention purposes.
                                </li>
                            </ul>
                            <p className="mt-3">
                                We do not use advertising cookies, social media tracking pixels, or analytics
                                services that track you across third-party websites.
                            </p>
                        </>
                    ),
                },
                {
                    title: "Data Retention",
                    content: (
                        <p>
                            We retain your personal data for as long as your account is active or as needed
                            to provide you the service. If you delete your account, we will delete your
                            personal data within 30 days, except where retention is required by law (e.g.,
                            billing records may be retained for up to 7 years for tax compliance).
                        </p>
                    ),
                },
                {
                    title: "Your Rights",
                    content: (
                        <>
                            <p>
                                Depending on your location, you may have the following rights regarding your
                                personal data:
                            </p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>
                                    <strong>Access:</strong> request a copy of the personal data we hold about
                                    you.
                                </li>
                                <li>
                                    <strong>Rectification:</strong> request correction of inaccurate data.
                                </li>
                                <li>
                                    <strong>Erasure:</strong> request deletion of your account and data.
                                </li>
                                <li>
                                    <strong>Portability:</strong> receive your data in a structured,
                                    machine-readable format.
                                </li>
                                <li>
                                    <strong>Objection / Restriction:</strong> object to or restrict certain
                                    processing activities.
                                </li>
                                <li>
                                    <strong>Withdraw consent:</strong> for health data processing at any time,
                                    which will result in account termination as health data is necessary to
                                    provide the service.
                                </li>
                            </ul>
                            <p className="mt-3">
                                To exercise any of these rights, contact us at{" "}
                                <a
                                    href="mailto:privacy@smartmoveai.com"
                                    className="text-blue-600 hover:underline"
                                >
                                    privacy@smartmoveai.com
                                </a>
                                . You also have the right to lodge a complaint with your local data protection
                                authority.
                            </p>
                        </>
                    ),
                },
                {
                    title: "Data Security",
                    content: (
                        <p>
                            We implement industry-standard security measures including HTTPS/TLS encryption
                            for all data in transit, encrypted database storage, HttpOnly and Secure cookies,
                            and restricted access controls. While we take security seriously, no system is
                            100% secure, and we cannot guarantee absolute security.
                        </p>
                    ),
                },
                {
                    title: "Children's Privacy",
                    content: (
                        <p>
                            Smart Move AI is not directed at children under the age of 16. We do not
                            knowingly collect personal data from children under 16. If we become aware that
                            a child under 16 has provided us with personal data, we will promptly delete it.
                        </p>
                    ),
                },
                {
                    title: "Changes to This Policy",
                    content: (
                        <p>
                            We may update this Privacy Policy from time to time. We will notify you of
                            significant changes by email or by displaying a prominent notice on the platform.
                            Continued use of the service after changes take effect constitutes your
                            acceptance of the updated policy.
                        </p>
                    ),
                },
                {
                    title: "Contact Us",
                    content: (
                        <p>
                            For any privacy-related questions, requests, or concerns, please contact our
                            Data Protection contact at{" "}
                            <a
                                href="mailto:privacy@smartmoveai.com"
                                className="text-blue-600 hover:underline"
                            >
                                privacy@smartmoveai.com
                            </a>
                            . You can also review our{" "}
                            <Link to="/terms" className="text-blue-600 hover:underline">
                                Terms of Service
                            </Link>
                            .
                        </p>
                    ),
                },
            ]}
        />
    );
}
