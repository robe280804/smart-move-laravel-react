import { Link } from "react-router";
import { LegalPage } from "@/components/welcome/LegalPage";

export function TermsOfService() {
    return (
        <LegalPage
            title="Terms of Service"
            subtitle="Please read these terms carefully before using Smart Move AI."
            lastUpdated="March 23, 2026"
            sections={[
                {
                    title: "Acceptance of Terms",
                    content: (
                        <p>
                            By creating an account or using Smart Move AI ("the Service"), you agree to be
                            bound by these Terms of Service ("Terms"). If you do not agree to these Terms,
                            do not use the Service. These Terms apply to all users, including free and paid
                            subscribers.
                        </p>
                    ),
                },
                {
                    title: "Description of Service",
                    content: (
                        <p>
                            Smart Move AI provides AI-powered personalized workout plan generation based on
                            user-provided fitness profiles and preferences. The Service includes workout plan
                            creation, exercise libraries, progress tracking features, and optional PDF export
                            capabilities depending on your subscription plan.
                        </p>
                    ),
                },
                {
                    title: "Eligibility",
                    content: (
                        <>
                            <p>To use the Service, you must:</p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>Be at least 16 years of age.</li>
                                <li>
                                    Have the legal capacity to enter into a binding contract in your
                                    jurisdiction.
                                </li>
                                <li>
                                    Not be prohibited from using the Service under applicable law.
                                </li>
                            </ul>
                        </>
                    ),
                },
                {
                    title: "User Accounts",
                    content: (
                        <>
                            <p>
                                You are responsible for maintaining the confidentiality of your account
                                credentials and for all activity that occurs under your account. You agree to:
                            </p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>Provide accurate and complete registration information.</li>
                                <li>
                                    Notify us immediately of any unauthorized use of your account at{" "}
                                    <a
                                        href="mailto:support@smartmoveai.com"
                                        className="text-blue-600 hover:underline"
                                    >
                                        support@smartmoveai.com
                                    </a>
                                    .
                                </li>
                                <li>Not share your account credentials with any third party.</li>
                                <li>Not create multiple accounts for abusive purposes.</li>
                            </ul>
                        </>
                    ),
                },
                {
                    title: "Subscriptions and Payments",
                    content: (
                        <>
                            <p>
                                Some features of the Service require a paid subscription. By subscribing,
                                you agree to the following:
                            </p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>
                                    Subscription fees are billed in advance on a recurring monthly or annual
                                    basis.
                                </li>
                                <li>
                                    Payments are processed by Stripe, Inc. and subject to their{" "}
                                    <a
                                        href="https://stripe.com/legal"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-blue-600 hover:underline"
                                    >
                                        Terms of Service
                                    </a>
                                    .
                                </li>
                                <li>
                                    You may cancel your subscription at any time. Cancellation takes effect at
                                    the end of the current billing period and you will retain access until
                                    then.
                                </li>
                                <li>
                                    We do not offer refunds for partial subscription periods, except where
                                    required by applicable law.
                                </li>
                                <li>
                                    We reserve the right to change subscription prices with at least 30 days'
                                    advance notice.
                                </li>
                            </ul>
                        </>
                    ),
                },
                {
                    title: "Health and Medical Disclaimer",
                    content: (
                        <>
                            <p className="font-medium text-slate-800">
                                IMPORTANT: Smart Move AI is not a medical service and does not provide
                                medical advice.
                            </p>
                            <p className="mt-2">
                                The workout plans and fitness recommendations generated by our AI are for
                                general informational and educational purposes only. They are not a
                                substitute for professional medical advice, diagnosis, or treatment from a
                                qualified healthcare provider or certified personal trainer.
                            </p>
                            <p className="mt-2">
                                You should consult your physician or other qualified health professional
                                before starting any new exercise program, especially if you have any
                                pre-existing medical conditions, injuries, or health concerns. By using
                                the Service, you acknowledge that you are participating in physical
                                activities at your own risk.
                            </p>
                        </>
                    ),
                },
                {
                    title: "Acceptable Use",
                    content: (
                        <>
                            <p>You agree not to use the Service to:</p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>Violate any applicable laws or regulations.</li>
                                <li>
                                    Attempt to gain unauthorized access to any part of the Service or its
                                    infrastructure.
                                </li>
                                <li>
                                    Scrape, copy, or redistribute Service content without our prior written
                                    consent.
                                </li>
                                <li>
                                    Submit false health information to manipulate AI-generated plans for
                                    distribution or resale.
                                </li>
                                <li>Interfere with the operation of the Service or other users' experience.</li>
                            </ul>
                        </>
                    ),
                },
                {
                    title: "Intellectual Property",
                    content: (
                        <p>
                            All content, features, and functionality of the Service — including but not
                            limited to the AI workout generation system, UI design, exercise database, and
                            branding — are owned by Smart Move AI and protected by applicable intellectual
                            property laws. The AI-generated workout plans created for your account are
                            licensed to you for personal, non-commercial use only.
                        </p>
                    ),
                },
                {
                    title: "Limitation of Liability",
                    content: (
                        <>
                            <p>
                                To the maximum extent permitted by applicable law, Smart Move AI shall not
                                be liable for any indirect, incidental, special, consequential, or punitive
                                damages arising from:
                            </p>
                            <ul className="list-disc list-inside space-y-1.5 mt-2">
                                <li>Your use of or inability to use the Service.</li>
                                <li>Any injuries or health consequences resulting from following workout plans.</li>
                                <li>Unauthorized access to or alteration of your data.</li>
                                <li>Any other matter relating to the Service.</li>
                            </ul>
                            <p className="mt-3">
                                Our total liability to you for any claim arising from these Terms shall not
                                exceed the amount you paid us in the 12 months preceding the claim.
                            </p>
                        </>
                    ),
                },
                {
                    title: "Termination",
                    content: (
                        <p>
                            You may delete your account at any time through your profile settings. We
                            reserve the right to suspend or terminate your account at our discretion if
                            you violate these Terms, engage in fraudulent activity, or if required by
                            law. Upon termination, your right to use the Service ceases immediately.
                        </p>
                    ),
                },
                {
                    title: "Changes to These Terms",
                    content: (
                        <p>
                            We may update these Terms from time to time. We will notify you of material
                            changes via email or a prominent notice on the platform at least 14 days before
                            the changes take effect. Your continued use of the Service after changes take
                            effect constitutes acceptance of the revised Terms.
                        </p>
                    ),
                },
                {
                    title: "Governing Law",
                    content: (
                        <p>
                            These Terms are governed by and construed in accordance with applicable law.
                            Any disputes arising from these Terms or your use of the Service shall be
                            subject to the exclusive jurisdiction of the competent courts in the applicable
                            jurisdiction.
                        </p>
                    ),
                },
                {
                    title: "Contact",
                    content: (
                        <p>
                            For questions about these Terms, contact us at{" "}
                            <a
                                href="mailto:legal@smartmoveai.com"
                                className="text-blue-600 hover:underline"
                            >
                                legal@smartmoveai.com
                            </a>
                            . You can also review our{" "}
                            <Link to="/privacy" className="text-blue-600 hover:underline">
                                Privacy Policy
                            </Link>
                            .
                        </p>
                    ),
                },
            ]}
        />
    );
}
